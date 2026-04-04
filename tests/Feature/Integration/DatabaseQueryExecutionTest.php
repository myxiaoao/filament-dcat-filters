<?php

use Cooper\FilamentDcatFilters\Exceptions\UnsupportedDatabaseDriverException;
use Cooper\FilamentDcatFilters\Filters\BetweenFilter;
use Cooper\FilamentDcatFilters\Filters\BooleanFilter;
use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;
use Cooper\FilamentDcatFilters\Filters\FilterGroup;
use Cooper\FilamentDcatFilters\Filters\FindInSetFilter;
use Cooper\FilamentDcatFilters\Filters\FullTextFilter;
use Cooper\FilamentDcatFilters\Filters\HiddenFilter;
use Cooper\FilamentDcatFilters\Filters\InFilter;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;
use Cooper\FilamentDcatFilters\Filters\NullFilter;
use Cooper\FilamentDcatFilters\Filters\RangeFilter;
use Cooper\FilamentDcatFilters\Filters\RegexFilter;
use Cooper\FilamentDcatFilters\Filters\SelectTableFilter;
use Cooper\FilamentDcatFilters\Tests\Fixtures\Models\TestCategory;
use Cooper\FilamentDcatFilters\Tests\Fixtures\Models\TestItem;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Schema;

/**
 * Helper: extract the query closure and invoke it on a real Eloquent Builder.
 */
function applyRealQuery(object $filter, Builder $query, array $data): Builder
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

    // Seed test data
    $media = TestCategory::create(['name' => 'Media']);
    $electronics = TestCategory::create(['name' => 'Electronics', 'parent_id' => $media->id]);
    $books = TestCategory::create(['name' => 'Books', 'parent_id' => $media->id]);

    TestItem::create([
        'title' => 'Laravel Guide',
        'description' => 'A comprehensive Laravel book',
        'status' => 'active',
        'price' => 49.99,
        'is_active' => true,
        'category_id' => $books->id,
        'tags' => 'php,laravel,web',
        'created_at' => '2024-06-15 10:00:00',
    ]);

    TestItem::create([
        'title' => 'Vue Tutorial',
        'description' => 'Frontend framework guide',
        'status' => 'active',
        'price' => 29.99,
        'is_active' => true,
        'category_id' => $books->id,
        'tags' => 'javascript,vue,frontend',
        'created_at' => '2024-08-20 10:00:00',
    ]);

    TestItem::create([
        'title' => 'Wireless Mouse',
        'description' => 'Ergonomic wireless mouse',
        'status' => 'inactive',
        'price' => 79.99,
        'is_active' => false,
        'deleted_at' => '2024-09-01 00:00:00',
        'category_id' => $electronics->id,
        'tags' => 'hardware,mouse',
        'created_at' => '2024-01-10 10:00:00',
    ]);

    TestItem::create([
        'title' => 'Mechanical Keyboard',
        'description' => null,
        'status' => 'active',
        'price' => 149.99,
        'is_active' => true,
        'category_id' => $electronics->id,
        'tags' => 'hardware,keyboard',
        'created_at' => '2025-02-01 10:00:00',
    ]);
});

afterEach(function () {
    Schema::dropIfExists('test_items');
    Schema::dropIfExists('test_categories');
});

// ─── Basic Filter Query Execution ─────────────────────────────────

describe('Basic Filter Execution', function () {
    it('LikeFilter matches by title', function () {
        $filter = LikeFilter::make('title');
        $results = applyRealQuery($filter, TestItem::query(), ['value' => 'Laravel'])->get();

        expect($results)->toHaveCount(1)
            ->and($results->first()->title)->toBe('Laravel Guide');
    });

    it('InFilter filters by status', function () {
        $filter = InFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive']);
        $results = applyRealQuery($filter, TestItem::query(), ['value' => 'inactive'])->get();

        expect($results)->toHaveCount(1)
            ->and($results->first()->title)->toBe('Wireless Mouse');
    });

    it('ComparisonFilter filters price >= 100', function () {
        $filter = ComparisonFilter::make('price')->gte();
        $results = applyRealQuery($filter, TestItem::query(), ['value' => '100'])->get();

        expect($results)->toHaveCount(1)
            ->and($results->first()->title)->toBe('Mechanical Keyboard');
    });

    it('BooleanFilter filters active items', function () {
        $filter = BooleanFilter::make('is_active');
        $results = applyRealQuery($filter, TestItem::query(), ['value' => '1'])->get();

        expect($results)->toHaveCount(3);
    });

    it('NullFilter filters deleted_at IS NULL', function () {
        $filter = NullFilter::make('deleted_at');
        $results = applyRealQuery($filter, TestItem::query(), ['value' => 'null'])->get();

        expect($results)->toHaveCount(3);
    });

    it('NullFilter filters deleted_at IS NOT NULL', function () {
        $filter = NullFilter::make('deleted_at');
        $results = applyRealQuery($filter, TestItem::query(), ['value' => 'not_null'])->get();

        expect($results)->toHaveCount(1)
            ->and($results->first()->title)->toBe('Wireless Mouse');
    });

    it('RangeFilter filters by date range', function () {
        $filter = RangeFilter::make('created_at')->date();
        $results = applyRealQuery($filter, TestItem::query(), [
            'from' => '2024-06-01',
            'to' => '2024-12-31',
        ])->get();

        expect($results)->toHaveCount(2)
            ->and($results->pluck('title')->sort()->values()->all())
            ->toBe(['Laravel Guide', 'Vue Tutorial']);
    });

    it('BetweenFilter filters by price range', function () {
        $filter = BetweenFilter::make('price');
        $results = applyRealQuery($filter, TestItem::query(), [
            'from' => '20',
            'to' => '50',
        ])->get();

        expect($results)->toHaveCount(2)
            ->and($results->pluck('title')->sort()->values()->all())
            ->toBe(['Laravel Guide', 'Vue Tutorial']);
    });

    it('HiddenFilter applies implicit condition', function () {
        $filter = HiddenFilter::make('status')->default('active')->eq();
        $results = applyRealQuery($filter, TestItem::query(), ['value' => 'active'])->get();

        expect($results)->toHaveCount(3);
    });
});

// ─── Combination Scenarios ─────────────────────────────────────────

describe('Combination Scenarios', function () {
    it('FilterGroup AND with namespaced data isolates filters', function () {
        $group = FilterGroup::make('search')
            ->andLogic()
            ->filters([
                LikeFilter::make('title_search')->column('title')->sensitive(),
                LikeFilter::make('desc_search')->column('description')->sensitive(),
            ]);

        $results = applyRealQuery($group, TestItem::query(), [
            'title_search' => ['value' => 'Laravel'],
            'desc_search' => ['value' => 'comprehensive'],
        ])->get();

        expect($results)->toHaveCount(1)
            ->and($results->first()->title)->toBe('Laravel Guide');
    });

    it('FilterGroup OR matches title or description', function () {
        $group = FilterGroup::make('search')
            ->orLogic()
            ->filters([
                LikeFilter::make('title_search')->column('title')->sensitive(),
                LikeFilter::make('desc_search')->column('description')->sensitive(),
            ]);

        // 'Mouse' in title, 'Laravel' in description
        $results = applyRealQuery($group, TestItem::query(), [
            'title_search' => ['value' => 'Mouse'],
            'desc_search' => ['value' => 'Laravel'],
        ])->get();

        expect($results)->toHaveCount(2)
            ->and($results->pluck('title')->sort()->values()->all())
            ->toBe(['Laravel Guide', 'Wireless Mouse']);
    });

    it('multi-filter stack applies AND combination', function () {
        $like = LikeFilter::make('title');
        $boolean = BooleanFilter::make('is_active');
        $comparison = ComparisonFilter::make('price')->gte();

        $query = TestItem::query();
        $query = applyRealQuery($like, $query, ['value' => 'a']);
        $query = applyRealQuery($boolean, $query, ['value' => '1']);
        $query = applyRealQuery($comparison, $query, ['value' => '40']);

        $results = $query->get();

        // 'a' in title + active + price >= 40 → Laravel Guide (49.99) + Mechanical Keyboard (149.99)
        expect($results)->toHaveCount(2);
    });

    it('InFilter with multiple values', function () {
        $filter = InFilter::make('status')
            ->options(['active' => 'Active', 'inactive' => 'Inactive'])
            ->multiple();
        $results = applyRealQuery($filter, TestItem::query(), ['values' => ['active', 'inactive']])->get();

        expect($results)->toHaveCount(4);
    });

    it('ComparisonFilter treats zero as valid value', function () {
        $filter = ComparisonFilter::make('price')->gte();
        $results = applyRealQuery($filter, TestItem::query(), ['value' => '0'])->get();

        expect($results)->toHaveCount(4);
    });
});

// ─── Relationship Scenarios ────────────────────────────────────────

describe('Relationship Scenarios', function () {
    it('LikeFilter with relationship filters by category name', function () {
        $filter = LikeFilter::make('category_name')
            ->relationship('category', 'name');
        $results = applyRealQuery($filter, TestItem::query(), ['value' => 'Electronics'])->get();

        expect($results)->toHaveCount(2)
            ->and($results->pluck('title')->sort()->values()->all())
            ->toBe(['Mechanical Keyboard', 'Wireless Mouse']);
    });

    it('InFilter with relationship filters by category name', function () {
        $filter = InFilter::make('category')
            ->relationship('category', 'name')
            ->options(['Books' => 'Books']);
        $results = applyRealQuery($filter, TestItem::query(), ['value' => 'Books'])->get();

        expect($results)->toHaveCount(2)
            ->and($results->pluck('title')->sort()->values()->all())
            ->toBe(['Laravel Guide', 'Vue Tutorial']);
    });

    it('SelectTableFilter filters by direct column via apply()', function () {
        $electronicsId = TestCategory::where('name', 'Electronics')->first()->id;

        $filter = SelectTableFilter::make('category_id')
            ->model(TestCategory::class);

        $query = TestItem::query();
        $filter->apply($query, ['value' => $electronicsId, 'isActive' => true]);
        $results = $query->get();

        expect($results)->toHaveCount(2)
            ->and($results->pluck('title')->sort()->values()->all())
            ->toBe(['Mechanical Keyboard', 'Wireless Mouse']);
    });
});

// ─── Nested Relationship (Deep Path) ───────────────────────────────

describe('Nested Relationship', function () {
    it('LikeFilter with nested relationship filters by category.parent name', function () {
        // category.parent = Media for both Electronics and Books
        $filter = LikeFilter::make('parent_category')
            ->relationship('category.parent', 'name');
        $results = applyRealQuery($filter, TestItem::query(), ['value' => 'Media'])->get();

        // All 4 items have categories whose parent is "Media"
        expect($results)->toHaveCount(4);
    });

    it('LikeFilter with nested relationship returns empty for non-matching parent', function () {
        $filter = LikeFilter::make('parent_category')
            ->relationship('category.parent', 'name');
        $results = applyRealQuery($filter, TestItem::query(), ['value' => 'NonExistent'])->get();

        expect($results)->toHaveCount(0);
    });
});

// ─── Database Driver Fail-Fast ─────────────────────────────────────

describe('Database Driver Fail-Fast', function () {
    it('FindInSetFilter throws on SQLite', function () {
        $filter = FindInSetFilter::make('tags')
            ->options(['php' => 'PHP']);

        expect(fn () => applyRealQuery($filter, TestItem::query(), ['value' => 'php']))
            ->toThrow(UnsupportedDatabaseDriverException::class);
    });

    it('RegexFilter throws on SQLite in user pattern mode', function () {
        $filter = RegexFilter::make('title');

        expect(fn () => applyRealQuery($filter, TestItem::query(), ['pattern' => '^test']))
            ->toThrow(UnsupportedDatabaseDriverException::class);
    });

    it('FullTextFilter does not throw on SQLite (degraded mode)', function () {
        $filter = FullTextFilter::make('search')
            ->searchIn(['title', 'description'])
            ->minLength(1);

        $results = applyRealQuery($filter, TestItem::query(), ['search' => 'Laravel'])->get();

        // Should use LIKE fallback, not throw
        expect($results)->toHaveCount(1)
            ->and($results->first()->title)->toBe('Laravel Guide');
    });
});
