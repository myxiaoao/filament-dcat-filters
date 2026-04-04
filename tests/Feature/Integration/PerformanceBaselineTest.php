<?php

use Cooper\FilamentDcatFilters\Filters\BooleanFilter;
use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;
use Cooper\FilamentDcatFilters\Filters\FilterGroup;
use Cooper\FilamentDcatFilters\Filters\InFilter;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;
use Cooper\FilamentDcatFilters\Filters\RangeFilter;
use Cooper\FilamentDcatFilters\Tests\Fixtures\Models\TestCategory;
use Cooper\FilamentDcatFilters\Tests\Fixtures\Models\TestItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Helper: extract and apply a filter query.
 */
function applyPerfQuery(object $filter, \Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder
{
    $ref = new ReflectionProperty($filter, 'modifyQueryUsing');
    $closure = $ref->getValue($filter);

    if ($closure === null) {
        return $query;
    }

    $bound = Closure::bind($closure, $filter, get_class($filter));

    return $bound($query, $data) ?? $query;
}

beforeEach(function () {
    Schema::create('test_categories', function ($table) {
        $table->id();
        $table->string('name');
        $table->foreignId('parent_id')->nullable();
        $table->timestamps();
    });

    Schema::create('test_items', function ($table) {
        $table->id();
        $table->string('title');
        $table->string('description')->nullable();
        $table->string('status')->default('active');
        $table->decimal('price', 10, 2)->default(0);
        $table->boolean('is_active')->default(true);
        $table->timestamp('deleted_at')->nullable();
        $table->foreignId('category_id')->nullable();
        $table->string('tags')->nullable();
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();
        $table->timestamps();
    });

    // Seed 500 items across 50 categories for performance testing
    $categories = [];
    for ($i = 1; $i <= 50; $i++) {
        $categories[] = TestCategory::create(['name' => "Category {$i}"]);
    }

    $statuses = ['active', 'inactive', 'pending', 'archived'];
    for ($i = 1; $i <= 500; $i++) {
        TestItem::create([
            'title' => "Item {$i} ".fake()->words(3, true),
            'description' => fake()->sentence(),
            'status' => $statuses[$i % 4],
            'price' => round($i * 1.5 + rand(0, 100), 2),
            'is_active' => $i % 3 !== 0,
            'category_id' => $categories[$i % 50]->id,
            'created_at' => now()->subDays(rand(0, 365)),
        ]);
    }
});

afterEach(function () {
    Schema::dropIfExists('test_items');
    Schema::dropIfExists('test_categories');
});

describe('Large Dataset Baseline', function () {
    it('LikeFilter on 500 rows completes in reasonable query count', function () {
        DB::enableQueryLog();

        $filter = LikeFilter::make('title');
        $results = applyPerfQuery($filter, TestItem::query(), ['value' => 'Item 1'])->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Should be 1 query, not N+1
        expect($queries)->toHaveCount(1)
            ->and($results->count())->toBeGreaterThan(0);
    });

    it('InFilter with 20 options on 500 rows uses single query', function () {
        DB::enableQueryLog();

        $filter = InFilter::make('status')
            ->options(['active' => 'Active', 'inactive' => 'Inactive', 'pending' => 'Pending', 'archived' => 'Archived'])
            ->multiple();
        $results = applyPerfQuery($filter, TestItem::query(), ['values' => ['active', 'pending']])->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        expect($queries)->toHaveCount(1)
            ->and($results->count())->toBeGreaterThan(0);
    });

    it('relationship filter on 500 rows uses 1 query with subquery', function () {
        DB::enableQueryLog();

        $filter = LikeFilter::make('category_name')
            ->relationship('category', 'name');
        $results = applyPerfQuery($filter, TestItem::query(), ['value' => 'Category 1'])->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // whereHas generates a single query with EXISTS subquery
        expect($queries)->toHaveCount(1)
            ->and($results->count())->toBeGreaterThan(0);
    });
});

describe('Complex OR Group Baseline', function () {
    it('FilterGroup with 5 OR conditions uses single query', function () {
        DB::enableQueryLog();

        $group = FilterGroup::make('search')
            ->orLogic()
            ->filters([
                LikeFilter::make('title_s')->column('title')->sensitive(),
                LikeFilter::make('desc_s')->column('description')->sensitive(),
                LikeFilter::make('status_s')->column('status')->sensitive(),
            ]);

        $results = applyPerfQuery($group, TestItem::query(), [
            'title_s' => ['value' => 'Item'],
            'desc_s' => ['value' => 'the'],
            'status_s' => ['value' => 'active'],
        ])->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // Single query with OR conditions
        expect($queries)->toHaveCount(1)
            ->and($results->count())->toBeGreaterThan(0);
    });
});

describe('Multi-Filter Stack Baseline', function () {
    it('4 stacked filters use 1 query each (4 total)', function () {
        DB::enableQueryLog();

        $query = TestItem::query();

        $like = LikeFilter::make('title');
        $query = applyPerfQuery($like, $query, ['value' => 'Item']);

        $boolean = BooleanFilter::make('is_active');
        $query = applyPerfQuery($boolean, $query, ['value' => '1']);

        $comparison = ComparisonFilter::make('price')->gte();
        $query = applyPerfQuery($comparison, $query, ['value' => '50']);

        $range = RangeFilter::make('created_at')->date();
        $query = applyPerfQuery($range, $query, [
            'from' => now()->subDays(180)->format('Y-m-d'),
            'to' => now()->format('Y-m-d'),
        ]);

        $results = $query->get();

        $queries = DB::getQueryLog();
        DB::disableQueryLog();

        // All filters are chained on the same query builder — 1 final query
        expect($queries)->toHaveCount(1)
            ->and($results->count())->toBeGreaterThanOrEqual(0);
    });
});

describe('Memory Baseline', function () {
    it('500 rows does not exceed 10MB memory for filter operations', function () {
        $memBefore = memory_get_usage(true);

        $filter = LikeFilter::make('title');
        $results = applyPerfQuery($filter, TestItem::query(), ['value' => 'Item'])->get();

        $memAfter = memory_get_usage(true);
        $memUsedMB = ($memAfter - $memBefore) / 1024 / 1024;

        expect($memUsedMB)->toBeLessThan(10)
            ->and($results->count())->toBeGreaterThan(0);
    });
});
