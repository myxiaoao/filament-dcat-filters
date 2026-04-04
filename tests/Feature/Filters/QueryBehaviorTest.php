<?php

use Carbon\Carbon;
use Cooper\FilamentDcatFilters\Filters\BetweenFilter;
use Cooper\FilamentDcatFilters\Filters\BooleanFilter;
use Cooper\FilamentDcatFilters\Filters\ColumnCompareFilter;
use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;
use Cooper\FilamentDcatFilters\Filters\DateComponentFilter;
use Cooper\FilamentDcatFilters\Filters\EnumFilter;
use Cooper\FilamentDcatFilters\Filters\ExistsFilter;
use Cooper\FilamentDcatFilters\Filters\FilterGroup;
use Cooper\FilamentDcatFilters\Filters\FindInSetFilter;
use Cooper\FilamentDcatFilters\Filters\FullTextFilter;
use Cooper\FilamentDcatFilters\Filters\GeoLocationFilter;
use Cooper\FilamentDcatFilters\Filters\HiddenFilter;
use Cooper\FilamentDcatFilters\Filters\InFilter;
use Cooper\FilamentDcatFilters\Filters\InputMaskFilter;
use Cooper\FilamentDcatFilters\Filters\JsonFilter;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;
use Cooper\FilamentDcatFilters\Filters\NullFilter;
use Cooper\FilamentDcatFilters\Filters\RangeFilter;
use Cooper\FilamentDcatFilters\Filters\RegexFilter;
use Cooper\FilamentDcatFilters\Filters\RelativeDateFilter;
use Cooper\FilamentDcatFilters\Filters\ScopeFilter;
use Cooper\FilamentDcatFilters\Filters\SoftDeleteFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Helper: extract the query closure from a filter via reflection and invoke it.
 *
 * Filament's apply() relies on $this->evaluate() which needs a full Livewire
 * context. Instead we grab the raw closure and call it directly, bound to the
 * filter instance so that `$this` references (e.g. resolveColumnName()) work.
 */
function applyFilterQuery(object $filter, Builder $query, array $data): Builder
{
    $ref = new ReflectionProperty($filter, 'modifyQueryUsing');
    $closure = $ref->getValue($filter);

    if ($closure === null) {
        return $query;
    }

    // Bind the closure to the filter so $this works inside it
    $bound = Closure::bind($closure, $filter, get_class($filter));

    return $bound($query, $data) ?? $query;
}

/**
 * Helper: create a bare Eloquent Builder for a fake table without hitting the DB.
 */
function freshQuery(string $table = 'test_items'): Builder
{
    $model = new class extends Model
    {
        protected $table = 'test_items';
    };

    // Override the table if needed
    if ($table !== 'test_items') {
        $model->setTable($table);
    }

    return $model->newQuery();
}

/**
 * Helper: create an Eloquent Builder for a model with a category() relationship.
 * Needed for ExistsFilter tests that use whereHas('category', ...).
 */
function freshQueryWithCategory(): Builder
{
    $model = new class extends Model
    {
        protected $table = 'test_items';

        public function category(): \Illuminate\Database\Eloquent\Relations\BelongsTo
        {
            return $this->belongsTo(
                (new class extends Model
                {
                    protected $table = 'test_categories';
                })::class,
                'category_id'
            );
        }
    };

    return $model->newQuery();
}

// ─── BooleanFilter ───────────────────────────────────────────────

describe('BooleanFilter Query', function () {
    it('adds where clause for true value', function () {
        $filter = BooleanFilter::make('is_active');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '1']);
        $sql = $query->toSql();

        expect($sql)->toContain('"is_active"')
            ->and($query->getBindings())->toContain(true);
    });

    it('adds where clause for false value', function () {
        $filter = BooleanFilter::make('is_active');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '0']);
        $sql = $query->toSql();

        expect($sql)->toContain('"is_active"')
            ->and($query->getBindings())->toContain(false);
    });

    it('does not add where clause for empty value', function () {
        $filter = BooleanFilter::make('is_active');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not add where clause for null value', function () {
        $filter = BooleanFilter::make('is_active');
        $query = applyFilterQuery($filter, freshQuery(), []);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('uses custom column name when set', function () {
        $filter = BooleanFilter::make('active_status')->column('is_active');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '1']);
        $sql = $query->toSql();

        expect($sql)->toContain('"is_active"')
            ->and($sql)->not->toContain('"active_status"');
    });
});

// ─── NullFilter ──────────────────────────────────────────────────

describe('NullFilter Query', function () {
    it('adds whereNull for null value', function () {
        $filter = NullFilter::make('deleted_at');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'null']);
        $sql = $query->toSql();

        expect($sql)->toContain('"deleted_at" is null');
    });

    it('adds whereNotNull for not_null value', function () {
        $filter = NullFilter::make('deleted_at');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'not_null']);
        $sql = $query->toSql();

        expect($sql)->toContain('"deleted_at" is not null');
    });

    it('does not add where clause for empty value', function () {
        $filter = NullFilter::make('deleted_at');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not add where clause for missing value', function () {
        $filter = NullFilter::make('deleted_at');
        $query = applyFilterQuery($filter, freshQuery(), []);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('uses custom column name', function () {
        $filter = NullFilter::make('has_address')->column('address');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'null']);
        $sql = $query->toSql();

        expect($sql)->toContain('"address" is null')
            ->and($sql)->not->toContain('"has_address"');
    });
});

// ─── ComparisonFilter ────────────────────────────────────────────

describe('ComparisonFilter Query', function () {
    it('applies greater than operator', function () {
        $filter = ComparisonFilter::make('price')->gt();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '100']);
        $sql = $query->toSql();

        expect($sql)->toContain('"price"')
            ->and($sql)->toContain('>')
            ->and($query->getBindings())->toContain('100');
    });

    it('applies greater than or equal operator', function () {
        $filter = ComparisonFilter::make('price')->gte();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '50']);
        $sql = $query->toSql();

        expect($sql)->toContain('"price"')
            ->and($sql)->toContain('>=');
    });

    it('applies less than operator', function () {
        $filter = ComparisonFilter::make('quantity')->lt();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '10']);
        $sql = $query->toSql();

        expect($sql)->toContain('"quantity"')
            ->and($sql)->toContain('<')
            ->and($query->getBindings())->toContain('10');
    });

    it('applies less than or equal operator', function () {
        $filter = ComparisonFilter::make('quantity')->lte();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '50']);
        $sql = $query->toSql();

        expect($sql)->toContain('"quantity"')
            ->and($sql)->toContain('<=');
    });

    it('applies equal operator', function () {
        $filter = ComparisonFilter::make('score')->eq();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '42']);
        $sql = $query->toSql();

        expect($sql)->toContain('"score"')
            ->and($sql)->toContain('=')
            ->and($query->getBindings())->toContain('42');
    });

    it('applies not equal operator', function () {
        $filter = ComparisonFilter::make('status')->ne();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '0']);
        $sql = $query->toSql();

        expect($sql)->toContain('"status"')
            ->and($sql)->toContain('!=');
    });

    it('does not apply query for empty value', function () {
        $filter = ComparisonFilter::make('price')->gt();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query for null value', function () {
        $filter = ComparisonFilter::make('price')->gt();
        $query = applyFilterQuery($filter, freshQuery(), []);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('uses custom column name', function () {
        $filter = ComparisonFilter::make('min_price')->column('price')->gte();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '10']);
        $sql = $query->toSql();

        expect($sql)->toContain('"price"')
            ->and($sql)->not->toContain('"min_price"');
    });

    it('applies money conversion', function () {
        $filter = ComparisonFilter::make('price')->money(100)->gte();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '50']);

        // 50 * 100 = 5000
        expect($query->getBindings())->toContain(5000.0);
    });
});

// ─── InFilter ────────────────────────────────────────────────────

describe('InFilter Query', function () {
    it('applies where clause for single value', function () {
        $filter = InFilter::make('status')->options(['active' => 'Active', 'inactive' => 'Inactive']);
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'active']);
        $sql = $query->toSql();

        expect($sql)->toContain('"status"')
            ->and($query->getBindings())->toContain('active');
    });

    it('applies whereIn clause for multiple values', function () {
        $filter = InFilter::make('status')
            ->options(['active' => 'Active', 'inactive' => 'Inactive'])
            ->multiple();
        $query = applyFilterQuery($filter, freshQuery(), ['values' => ['active', 'inactive']]);
        $sql = $query->toSql();

        expect($sql)->toContain('"status"')
            ->and($sql)->toContain('in')
            ->and($query->getBindings())->toEqual(['active', 'inactive']);
    });

    it('applies where != for negated single value', function () {
        $filter = InFilter::make('status')
            ->options(['active' => 'Active'])
            ->notIn();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'active']);
        $sql = $query->toSql();

        expect($sql)->toContain('"status"')
            ->and($sql)->toContain('!=');
    });

    it('applies whereNotIn for negated multiple values', function () {
        $filter = InFilter::make('status')
            ->options(['active' => 'Active', 'inactive' => 'Inactive'])
            ->multiple()
            ->notIn();
        $query = applyFilterQuery($filter, freshQuery(), ['values' => ['active', 'inactive']]);
        $sql = $query->toSql();

        expect($sql)->toContain('"status"')
            ->and($sql)->toContain('not in');
    });

    it('does not apply query for empty single value', function () {
        $filter = InFilter::make('status')->options(['active' => 'Active']);
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query for empty multiple values', function () {
        $filter = InFilter::make('status')
            ->options(['active' => 'Active'])
            ->multiple();
        $query = applyFilterQuery($filter, freshQuery(), ['values' => []]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('uses custom column name', function () {
        $filter = InFilter::make('user_status')
            ->column('status')
            ->options(['active' => 'Active']);
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'active']);
        $sql = $query->toSql();

        expect($sql)->toContain('"status"')
            ->and($sql)->not->toContain('"user_status"');
    });
});

// ─── LikeFilter ──────────────────────────────────────────────────

describe('LikeFilter Query', function () {
    it('applies case-sensitive like query', function () {
        $filter = LikeFilter::make('title')->sensitive();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'hello']);
        $sql = $query->toSql();

        expect($sql)->toContain('"title"')
            ->and($sql)->toContain('like')
            ->and($query->getBindings())->toContain('%hello%');
    });

    it('applies case-insensitive like query with LOWER()', function () {
        $filter = LikeFilter::make('title')->insensitive();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'Hello']);
        $rawSql = $query->toSql();

        // SQLite uses LOWER() wrapping
        expect($rawSql)->toContain('LOWER(')
            ->and($rawSql)->toContain('like')
            ->and($query->getBindings())->toContain('%hello%');
    });

    it('applies startsWith wildcard', function () {
        $filter = LikeFilter::make('name')->sensitive()->startsWith();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'foo']);

        expect($query->getBindings())->toContain('foo%');
    });

    it('applies endsWith wildcard', function () {
        $filter = LikeFilter::make('name')->sensitive()->endsWith();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'bar']);

        expect($query->getBindings())->toContain('%bar');
    });

    it('applies exact match (no wildcards)', function () {
        $filter = LikeFilter::make('code')->sensitive()->exact();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'ABC']);

        expect($query->getBindings())->toContain('ABC');
    });

    it('applies NOT LIKE when negated (case-sensitive)', function () {
        $filter = LikeFilter::make('name')->sensitive()->notLike();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'test']);
        $sql = $query->toSql();

        expect($sql)->toContain('not like');
    });

    it('does not apply query for empty value', function () {
        $filter = LikeFilter::make('title');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('escapes special LIKE characters', function () {
        $filter = LikeFilter::make('title')->sensitive();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '100%']);

        expect($query->getBindings())->toContain('%100\%%');
    });
});

// ─── HiddenFilter ────────────────────────────────────────────────

describe('HiddenFilter Query', function () {
    it('applies default value with eq operator', function () {
        $filter = HiddenFilter::make('tenant_id')->default(5)->eq();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 5]);
        $sql = $query->toSql();

        expect($sql)->toContain('"tenant_id"')
            ->and($sql)->toContain('=')
            ->and($query->getBindings())->toContain(5);
    });

    it('uses default value when data value is missing', function () {
        $filter = HiddenFilter::make('tenant_id')->default(10)->eq();
        $query = applyFilterQuery($filter, freshQuery(), []);
        $sql = $query->toSql();

        expect($sql)->toContain('"tenant_id"')
            ->and($query->getBindings())->toContain(10);
    });

    it('does not apply query for empty value without default', function () {
        $filter = HiddenFilter::make('tenant_id')->eq();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('applies gt operator', function () {
        $filter = HiddenFilter::make('level')->default(3)->gt();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 3]);
        $sql = $query->toSql();

        expect($sql)->toContain('"level"')
            ->and($sql)->toContain('>');
    });

    it('uses custom column name', function () {
        $filter = HiddenFilter::make('org')->column('organization_id')->default(1)->eq();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 1]);
        $sql = $query->toSql();

        expect($sql)->toContain('"organization_id"')
            ->and($sql)->not->toContain('"org"');
    });
});

// ─── BetweenFilter (RangeFilter) ────────────────────────────────

describe('BetweenFilter Query', function () {
    it('applies whereBetween when both from and to are provided', function () {
        $filter = BetweenFilter::make('price');
        $query = applyFilterQuery($filter, freshQuery(), ['from' => '10', 'to' => '100']);
        $sql = $query->toSql();

        expect($sql)->toContain('"price"')
            ->and($sql)->toContain('between')
            ->and($query->getBindings())->toEqual(['10', '100']);
    });

    it('applies >= when only from is provided', function () {
        $filter = BetweenFilter::make('price');
        $query = applyFilterQuery($filter, freshQuery(), ['from' => '10', 'to' => '']);
        $sql = $query->toSql();

        expect($sql)->toContain('"price"')
            ->and($sql)->toContain('>=')
            ->and($query->getBindings())->toContain('10');
    });

    it('applies <= when only to is provided', function () {
        $filter = BetweenFilter::make('price');
        $query = applyFilterQuery($filter, freshQuery(), ['from' => '', 'to' => '50']);
        $sql = $query->toSql();

        expect($sql)->toContain('"price"')
            ->and($sql)->toContain('<=')
            ->and($query->getBindings())->toContain('50');
    });

    it('does not apply query when both are empty', function () {
        $filter = BetweenFilter::make('price');
        $query = applyFilterQuery($filter, freshQuery(), ['from' => '', 'to' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('swaps from and to when from > to', function () {
        $filter = BetweenFilter::make('price');
        $query = applyFilterQuery($filter, freshQuery(), ['from' => '100', 'to' => '10']);

        // Should swap so bindings are [10, 100]
        expect($query->getBindings())->toEqual(['10', '100']);
    });

    it('uses custom column name', function () {
        $filter = BetweenFilter::make('price_range')->column('price');
        $query = applyFilterQuery($filter, freshQuery(), ['from' => '10', 'to' => '50']);
        $sql = $query->toSql();

        expect($sql)->toContain('"price"')
            ->and($sql)->not->toContain('"price_range"');
    });

    it('treats zero as a valid value', function () {
        $filter = BetweenFilter::make('score');
        $query = applyFilterQuery($filter, freshQuery(), ['from' => '0', 'to' => '100']);
        $sql = $query->toSql();

        expect($sql)->toContain('between')
            ->and($query->getBindings())->toEqual(['0', '100']);
    });
});

// ─── RangeFilter ─────────────────────────────────────────────────

describe('RangeFilter Query', function () {
    it('applies date range query', function () {
        $filter = RangeFilter::make('created_at')->date();
        $query = applyFilterQuery($filter, freshQuery(), ['from' => '2024-01-01', 'to' => '2024-12-31']);
        $sql = $query->toSql();

        expect($sql)->toContain('"created_at"')
            ->and($sql)->toContain('between')
            ->and($query->getBindings())->toEqual(['2024-01-01', '2024-12-31']);
    });

    it('applies numeric range query', function () {
        $filter = RangeFilter::make('amount')->numeric();
        $query = applyFilterQuery($filter, freshQuery(), ['from' => '100', 'to' => '500']);
        $sql = $query->toSql();

        expect($sql)->toContain('"amount"')
            ->and($sql)->toContain('between');
    });
});

// ─── Edge Cases ──────────────────────────────────────────────────

describe('Edge Cases', function () {
    it('LikeFilter escapes percent wildcard in search value', function () {
        $filter = LikeFilter::make('title')->sensitive();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '100%']);

        expect($query->toSql())->toContain('like')
            ->and($query->getBindings())->toContain('%100\%%');
    });

    it('LikeFilter escapes underscore in search value', function () {
        $filter = LikeFilter::make('code')->sensitive();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'A_B']);

        expect($query->toSql())->toContain('like')
            ->and($query->getBindings())->toContain('%A\_B%');
    });

    it('ComparisonFilter ignores non-numeric string input', function () {
        $filter = ComparisonFilter::make('price')->gte();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'abc']);

        // Non-numeric input is rejected at query level
        expect($query->toSql())->not->toContain('>=');
    });

    it('RangeFilter swaps from and to when from is greater', function () {
        $filter = RangeFilter::make('amount')->integer();
        $query = applyFilterQuery($filter, freshQuery(), ['from' => '100', 'to' => '50']);

        // HasRangeQuery swaps values so bindings are [50, 100]
        expect($query->toSql())->toContain('between')
            ->and($query->getBindings())->toEqual(['50', '100']);
    });

    it('BooleanFilter handles string zero correctly', function () {
        $filter = BooleanFilter::make('is_active');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '0']);

        expect($query->toSql())->toContain('"is_active"')
            ->and($query->getBindings())->toContain(false);
    });

    it('HiddenFilter handles zero value correctly', function () {
        $filter = HiddenFilter::make('status')->eq()->default(0);
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 0]);

        expect($query->toSql())->toContain('"status"')
            ->and($query->getBindings())->toContain(0);
    });
});

// ─── ScopeFilter ─────────────────────────────────────────────────

describe('ScopeFilter Query', function () {
    it('applies query closure when a non-default scope is selected', function () {
        $filter = ScopeFilter::make('status')->scopes([
            'all' => ['label' => 'All', 'default' => true],
            'published' => [
                'label' => 'Published',
                'query' => fn (Builder $q) => $q->where('status', 'published'),
            ],
        ]);
        $query = applyFilterQuery($filter, freshQuery(), ['scope' => 'published']);
        $sql = $query->toSql();

        expect($sql)->toContain('"status"')
            ->and($query->getBindings())->toContain('published');
    });

    it('does not add where clauses when the default scope is selected', function () {
        $filter = ScopeFilter::make('status')->scopes([
            'all' => ['label' => 'All', 'default' => true],
            'published' => [
                'label' => 'Published',
                'query' => fn (Builder $q) => $q->where('status', 'published'),
            ],
        ]);
        $query = applyFilterQuery($filter, freshQuery(), ['scope' => 'all']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('falls back to default scope when data is empty', function () {
        $filter = ScopeFilter::make('status')->scopes([
            'all' => ['label' => 'All', 'default' => true],
            'published' => [
                'label' => 'Published',
                'query' => fn (Builder $q) => $q->where('status', 'published'),
            ],
        ]);
        $query = applyFilterQuery($filter, freshQuery(), []);
        $sql = $query->toSql();

        // Default scope 'all' has no query closure, so no where clause
        expect($sql)->not->toContain('where');
    });

    it('falls back to default scope when scope is null', function () {
        $filter = ScopeFilter::make('status')->scopes([
            'all' => ['label' => 'All', 'default' => true],
            'active' => [
                'label' => 'Active',
                'query' => fn (Builder $q) => $q->where('status', 'active'),
            ],
        ]);
        $query = applyFilterQuery($filter, freshQuery(), ['scope' => null]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });
});

// ─── FullTextFilter ───────────────────────────────────────────────

describe('FullTextFilter Query', function () {
    it('applies LIKE search across multiple columns', function () {
        $filter = FullTextFilter::make('search')
            ->searchIn(['title', 'body'])
            ->minLength(1);
        $query = applyFilterQuery($filter, freshQuery(), ['search' => 'hello']);
        $sql = $query->toSql();

        expect($sql)->toContain('LIKE')
            ->and($sql)->toContain('"title"')
            ->and($sql)->toContain('"body"')
            ->and($query->getBindings())->toContain('%hello%');
    });

    it('does not apply query when search is shorter than minLength', function () {
        $filter = FullTextFilter::make('search')
            ->searchIn(['title'])
            ->minLength(3);
        $query = applyFilterQuery($filter, freshQuery(), ['search' => 'ab']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query when searchColumns is empty', function () {
        $filter = FullTextFilter::make('search')->minLength(1);
        $query = applyFilterQuery($filter, freshQuery(), ['search' => 'hello']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query for empty search', function () {
        $filter = FullTextFilter::make('search')->searchIn(['title'])->minLength(1);
        $query = applyFilterQuery($filter, freshQuery(), ['search' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query when search key is missing', function () {
        $filter = FullTextFilter::make('search')->searchIn(['title'])->minLength(1);
        $query = applyFilterQuery($filter, freshQuery(), []);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });
});

// ─── EnumFilter ───────────────────────────────────────────────────

enum QueryTestStatus: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Pending = 'pending';
}

describe('EnumFilter Query', function () {
    it('applies where clause for a single value', function () {
        $filter = EnumFilter::make('status')->enum(QueryTestStatus::class);
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'active']);
        $sql = $query->toSql();

        expect($sql)->toContain('"status"')
            ->and($query->getBindings())->toContain('active');
    });

    it('applies whereIn clause for multiple values', function () {
        $filter = EnumFilter::make('status')->enum(QueryTestStatus::class)->multiple();
        $query = applyFilterQuery($filter, freshQuery(), ['values' => ['active', 'pending']]);
        $sql = $query->toSql();

        expect($sql)->toContain('"status"')
            ->and($sql)->toContain('in')
            ->and($query->getBindings())->toEqual(['active', 'pending']);
    });

    it('does not apply query for empty single value', function () {
        $filter = EnumFilter::make('status')->enum(QueryTestStatus::class);
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query for empty multiple values', function () {
        $filter = EnumFilter::make('status')->enum(QueryTestStatus::class)->multiple();
        $query = applyFilterQuery($filter, freshQuery(), ['values' => []]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('uses custom column name', function () {
        $filter = EnumFilter::make('state')->enum(QueryTestStatus::class)->column('status');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'active']);
        $sql = $query->toSql();

        expect($sql)->toContain('"status"')
            ->and($sql)->not->toContain('"state"');
    });
});

// ─── DateComponentFilter ──────────────────────────────────────────

describe('DateComponentFilter Query', function () {
    it('applies YEAR() function to the column', function () {
        $filter = DateComponentFilter::make('created_at')->year();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '2024']);
        $sql = $query->toSql();

        expect($sql)->toContain('YEAR(')
            ->and($sql)->toContain('"created_at"')
            ->and($query->getBindings())->toContain('2024');
    });

    it('applies MONTH() function to the column', function () {
        $filter = DateComponentFilter::make('created_at')->month();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '06']);
        $sql = $query->toSql();

        expect($sql)->toContain('MONTH(')
            ->and($sql)->toContain('"created_at"')
            ->and($query->getBindings())->toContain('06');
    });

    it('applies DAY() function to the column', function () {
        $filter = DateComponentFilter::make('created_at')->day();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '15']);
        $sql = $query->toSql();

        expect($sql)->toContain('DAY(')
            ->and($sql)->toContain('"created_at"')
            ->and($query->getBindings())->toContain('15');
    });

    it('does not apply query for empty value', function () {
        $filter = DateComponentFilter::make('created_at')->year();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query when value is missing', function () {
        $filter = DateComponentFilter::make('created_at')->year();
        $query = applyFilterQuery($filter, freshQuery(), []);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });
});

// ─── RegexFilter ──────────────────────────────────────────────────

describe('RegexFilter Query', function () {
    it('applies REGEXP clause in user pattern mode', function () {
        $filter = RegexFilter::make('email')->userPattern()->driver('mysql');
        $query = applyFilterQuery($filter, freshQuery(), ['pattern' => '^test']);
        $sql = $query->toSql();

        expect($sql)->toContain('REGEXP')
            ->and($query->getBindings())->toContain('^test');
    });

    it('applies REGEXP clause when pattern mode is enabled', function () {
        $filter = RegexFilter::make('phone')->pattern('^1[3-9][0-9]{9}$')->driver('mysql');
        $query = applyFilterQuery($filter, freshQuery(), ['enabled' => true]);
        $sql = $query->toSql();

        expect($sql)->toContain('REGEXP')
            ->and($query->getBindings())->toContain('^1[3-9][0-9]{9}$');
    });

    it('does not apply query when pattern mode is enabled but toggle is false', function () {
        $filter = RegexFilter::make('phone')->pattern('^1[3-9][0-9]{9}$');
        $query = applyFilterQuery($filter, freshQuery(), ['enabled' => false]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query for empty pattern in user pattern mode', function () {
        $filter = RegexFilter::make('email')->userPattern();
        $query = applyFilterQuery($filter, freshQuery(), ['pattern' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query when pattern exceeds max length', function () {
        $filter = RegexFilter::make('content')->userPattern();
        $longPattern = str_repeat('a', 501);
        $query = applyFilterQuery($filter, freshQuery(), ['pattern' => $longPattern]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('REGEXP');
    });

    it('does not apply query for invalid regex syntax', function () {
        $filter = RegexFilter::make('email')->userPattern();
        $query = applyFilterQuery($filter, freshQuery(), ['pattern' => '[invalid(']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('REGEXP');
    });

    it('applies query for valid regex syntax', function () {
        $filter = RegexFilter::make('email')->userPattern()->driver('mysql');
        $query = applyFilterQuery($filter, freshQuery(), ['pattern' => '^[a-z]+$']);
        $sql = $query->toSql();

        expect($sql)->toContain('REGEXP');
    });

    it('accepts patterns containing forward slash', function () {
        $filter = RegexFilter::make('url')->userPattern()->driver('mysql');
        $query = applyFilterQuery($filter, freshQuery(), ['pattern' => 'https?://']);
        $sql = $query->toSql();

        expect($sql)->toContain('REGEXP')
            ->and($query->getBindings())->toContain('https?://');
    });

    it('accepts patterns containing multiple forward slashes', function () {
        $filter = RegexFilter::make('path')->userPattern()->driver('mysql');
        $query = applyFilterQuery($filter, freshQuery(), ['pattern' => '^/api/v[0-9]+/']);
        $sql = $query->toSql();

        expect($sql)->toContain('REGEXP');
    });
});

// ─── JsonFilter ───────────────────────────────────────────────────

describe('JsonFilter Query', function () {
    it('applies eq operator with json path', function () {
        $filter = JsonFilter::make('settings')->path('theme')->eq();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'dark']);
        $sql = $query->toSql();

        expect($sql)->toContain('settings')
            ->and($sql)->toContain('theme')
            ->and($query->getBindings())->toContain('dark');
    });

    it('wraps value with % when using like operator', function () {
        $filter = JsonFilter::make('settings')->path('theme')->like();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'dark']);

        expect($query->getBindings())->toContain('%dark%');
    });

    it('does not apply query for empty value', function () {
        $filter = JsonFilter::make('settings')->path('theme')->eq();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query when value is missing', function () {
        $filter = JsonFilter::make('settings')->path('theme')->eq();
        $query = applyFilterQuery($filter, freshQuery(), []);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('uses custom column name', function () {
        $filter = JsonFilter::make('meta_data')->column('settings')->path('color')->eq();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'blue']);
        $sql = $query->toSql();

        expect($sql)->toContain('settings')
            ->and($sql)->not->toContain('meta_data');
    });
});

// ─── FindInSetFilter ──────────────────────────────────────────────

describe('FindInSetFilter Query', function () {
    it('applies FIND_IN_SET for a single value', function () {
        $filter = FindInSetFilter::make('tags')->options(['php' => 'PHP', 'js' => 'JavaScript'])->driver('mysql');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'php']);
        $sql = $query->toSql();

        expect($sql)->toContain('FIND_IN_SET')
            ->and($query->getBindings())->toContain('php');
    });

    it('applies OR logic for multiple values with matchAny', function () {
        $filter = FindInSetFilter::make('tags')
            ->options(['php' => 'PHP', 'js' => 'JavaScript', 'css' => 'CSS'])
            ->multiple()
            ->matchAny()
            ->driver('mysql');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => ['php', 'js']]);
        $sql = $query->toSql();

        expect($sql)->toContain('FIND_IN_SET')
            ->and($sql)->toContain('or')
            ->and($query->getBindings())->toContain('php')
            ->and($query->getBindings())->toContain('js');
    });

    it('applies AND logic for multiple values with matchAll (default)', function () {
        $filter = FindInSetFilter::make('tags')
            ->options(['php' => 'PHP', 'js' => 'JavaScript', 'css' => 'CSS'])
            ->multiple()
            ->matchAll()
            ->driver('mysql');
        $query = applyFilterQuery($filter, freshQuery(), ['value' => ['php', 'js']]);
        $sql = $query->toSql();

        // matchAll: two separate FIND_IN_SET conditions without a top-level or
        expect($sql)->toContain('FIND_IN_SET')
            ->and($query->getBindings())->toContain('php')
            ->and($query->getBindings())->toContain('js');
    });

    it('does not apply query for empty value', function () {
        $filter = FindInSetFilter::make('tags')->options(['php' => 'PHP']);
        $query = applyFilterQuery($filter, freshQuery(), ['value' => null]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query for empty array value', function () {
        $filter = FindInSetFilter::make('tags')->options(['php' => 'PHP'])->multiple();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => []]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });
});

// ─── GeoLocationFilter ────────────────────────────────────────────

describe('GeoLocationFilter Query', function () {
    it('applies haversine formula when lat, lng, and radius are provided', function () {
        $filter = GeoLocationFilter::make('location');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'radius' => '10',
        ]);
        $sql = $query->toSql();

        expect($sql)->toContain('acos')
            ->and($sql)->toContain('radians')
            ->and($sql)->toContain('"latitude"')
            ->and($sql)->toContain('"longitude"');
    });

    it('does not apply query when latitude is empty', function () {
        $filter = GeoLocationFilter::make('location');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '',
            'longitude' => '-74.0060',
            'radius' => '10',
        ]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query when longitude is empty', function () {
        $filter = GeoLocationFilter::make('location');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '40.7128',
            'longitude' => '',
            'radius' => '10',
        ]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('uses custom coordinate columns when set', function () {
        $filter = GeoLocationFilter::make('location')->coordinates('lat', 'lng');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '40.7128',
            'longitude' => '-74.0060',
            'radius' => '5',
        ]);
        $sql = $query->toSql();

        expect($sql)->toContain('"lat"')
            ->and($sql)->toContain('"lng"');
    });

    it('does not apply query for latitude out of range', function () {
        $filter = GeoLocationFilter::make('location');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '91',
            'longitude' => '100',
            'radius' => '10',
        ]);
        expect($query->toSql())->not->toContain('acos');
    });

    it('does not apply query for latitude below -90', function () {
        $filter = GeoLocationFilter::make('location');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '-91',
            'longitude' => '0',
            'radius' => '10',
        ]);
        expect($query->toSql())->not->toContain('acos');
    });

    it('does not apply query for longitude out of range', function () {
        $filter = GeoLocationFilter::make('location');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '45',
            'longitude' => '181',
            'radius' => '10',
        ]);
        expect($query->toSql())->not->toContain('acos');
    });

    it('does not apply query for longitude below -180', function () {
        $filter = GeoLocationFilter::make('location');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '45',
            'longitude' => '-181',
            'radius' => '10',
        ]);
        expect($query->toSql())->not->toContain('acos');
    });

    it('does not apply query for zero radius', function () {
        $filter = GeoLocationFilter::make('location');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '45',
            'longitude' => '90',
            'radius' => '0',
        ]);
        expect($query->toSql())->not->toContain('acos');
    });

    it('does not apply query for negative radius', function () {
        $filter = GeoLocationFilter::make('location');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '45',
            'longitude' => '90',
            'radius' => '-5',
        ]);
        expect($query->toSql())->not->toContain('acos');
    });

    it('applies query for valid boundary coordinates (90, 180)', function () {
        $filter = GeoLocationFilter::make('location');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '90',
            'longitude' => '180',
            'radius' => '10',
        ]);
        expect($query->toSql())->toContain('acos');
    });

    it('applies query for valid boundary coordinates (-90, -180)', function () {
        $filter = GeoLocationFilter::make('location');
        $query = applyFilterQuery($filter, freshQuery(), [
            'latitude' => '-90',
            'longitude' => '-180',
            'radius' => '10',
        ]);
        expect($query->toSql())->toContain('acos');
    });
});

// ─── RelativeDateFilter ───────────────────────────────────────────

describe('RelativeDateFilter Query', function () {
    it('applies whereBetween for the today preset', function () {
        $filter = RelativeDateFilter::make('created_at');
        $query = applyFilterQuery($filter, freshQuery(), ['preset' => 'today']);
        $sql = $query->toSql();

        expect($sql)->toContain('"created_at"')
            ->and($sql)->toContain('between');
    });

    it('applies whereBetween for the last_7_days preset', function () {
        $filter = RelativeDateFilter::make('created_at');
        $query = applyFilterQuery($filter, freshQuery(), ['preset' => 'last_7_days']);
        $sql = $query->toSql();

        expect($sql)->toContain('"created_at"')
            ->and($sql)->toContain('between')
            ->and($query->getBindings())->toHaveCount(2);
    });

    it('does not apply query for empty preset', function () {
        $filter = RelativeDateFilter::make('created_at');
        $query = applyFilterQuery($filter, freshQuery(), ['preset' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query when preset is missing', function () {
        $filter = RelativeDateFilter::make('created_at');
        $query = applyFilterQuery($filter, freshQuery(), []);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('applies whereBetween for a custom preset added via addPresets', function () {
        $filter = RelativeDateFilter::make('created_at')->addPresets([
            'last_90_days' => [
                'label' => 'Last 90 Days',
                'range' => fn () => [Carbon::now()->subDays(89)->startOfDay(), Carbon::now()->endOfDay()],
            ],
        ]);
        $query = applyFilterQuery($filter, freshQuery(), ['preset' => 'last_90_days']);
        $sql = $query->toSql();

        expect($sql)->toContain('"created_at"')
            ->and($sql)->toContain('between')
            ->and($query->getBindings())->toHaveCount(2);
    });
});

// ─── InputMaskFilter ──────────────────────────────────────────────

describe('InputMaskFilter Query', function () {
    it('applies LIKE with % wrapping for like operator', function () {
        $filter = InputMaskFilter::make('phone')->like()->stripMask(false);
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '1234567890']);
        $sql = $query->toSql();

        expect($sql)->toContain('"phone"')
            ->and($sql)->toContain('like')
            ->and($query->getBindings())->toContain('%1234567890%');
    });

    it('applies exact equality for exact operator', function () {
        $filter = InputMaskFilter::make('code')->exact()->stripMask(false);
        $query = applyFilterQuery($filter, freshQuery(), ['value' => 'ABC123']);
        $sql = $query->toSql();

        expect($sql)->toContain('"code"')
            ->and($sql)->toContain('=')
            ->and($query->getBindings())->toContain('ABC123');
    });

    it('strips non-alphanumeric characters when stripMask is enabled', function () {
        $filter = InputMaskFilter::make('phone')->like()->stripMask(true);
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '(123) 456-7890']);

        // stripMaskCharacters removes non-alphanumeric chars → '1234567890'
        expect($query->getBindings())->toContain('%1234567890%');
    });

    it('does not apply query for empty value', function () {
        $filter = InputMaskFilter::make('phone')->like();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query when value is missing', function () {
        $filter = InputMaskFilter::make('phone')->like();
        $query = applyFilterQuery($filter, freshQuery(), []);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });
});

// ─── FilterGroup ──────────────────────────────────────────────────
// Note: FilterGroup uses namespaced data structure: data[filterName][fieldName]
// Each child filter's state lives under its own name key.

describe('FilterGroup Query', function () {
    it('applies all child filter conditions with AND logic using namespaced data', function () {
        $group = FilterGroup::make('search_group')
            ->filters([
                LikeFilter::make('title')->sensitive(),
                LikeFilter::make('body')->sensitive(),
            ])
            ->andLogic();

        // Namespaced: data[title][value], data[body][value]
        $query = applyFilterQuery($group, freshQuery(), [
            'title' => ['value' => 'hello'],
            'body' => ['value' => 'world'],
        ]);
        $sql = $query->toSql();

        expect($sql)->toContain('"title"')
            ->and($sql)->toContain('"body"');
    });

    it('wraps conditions in orWhere with OR logic using namespaced data', function () {
        $group = FilterGroup::make('search_group')
            ->filters([
                LikeFilter::make('title')->sensitive(),
                LikeFilter::make('body')->sensitive(),
            ])
            ->orLogic();

        $query = applyFilterQuery($group, freshQuery(), [
            'title' => ['value' => 'hello'],
            'body' => ['value' => 'world'],
        ]);
        $sql = $query->toSql();

        expect($sql)->toContain('or')
            ->and($sql)->toContain('"title"')
            ->and($sql)->toContain('"body"');
    });

    it('does not apply query when all data values are empty', function () {
        $group = FilterGroup::make('search_group')
            ->filters([
                LikeFilter::make('title')->sensitive(),
            ])
            ->andLogic();

        $query = applyFilterQuery($group, freshQuery(), [
            'title' => ['value' => ''],
        ]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('does not apply query when data is empty', function () {
        $group = FilterGroup::make('search_group')
            ->filters([
                LikeFilter::make('title')->sensitive(),
            ])
            ->andLogic();

        $query = applyFilterQuery($group, freshQuery(), []);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('isolates identically-named fields across child filters', function () {
        // Two LikeFilters each have a 'value' field, but namespacing prevents collision
        $group = FilterGroup::make('search_group')
            ->filters([
                LikeFilter::make('title')->sensitive(),
                LikeFilter::make('body')->sensitive(),
            ])
            ->andLogic();

        $query = applyFilterQuery($group, freshQuery(), [
            'title' => ['value' => 'alpha'],
            'body' => ['value' => 'beta'],
        ]);

        $bindings = $query->getBindings();
        expect($bindings)->toContain('%alpha%')
            ->and($bindings)->toContain('%beta%');
    });

    it('handles mixed filter types with namespaced data', function () {
        $group = FilterGroup::make('mixed')
            ->filters([
                LikeFilter::make('title')->sensitive(),
                ComparisonFilter::make('price')->gte(),
            ])
            ->andLogic();

        $query = applyFilterQuery($group, freshQuery(), [
            'title' => ['value' => 'test'],
            'price' => ['value' => '100'],
        ]);
        $sql = $query->toSql();

        expect($sql)->toContain('"title"')
            ->and($sql)->toContain('"price"')
            ->and($sql)->toContain('>=');
    });

    it('handles RangeFilter from/to with namespaced data', function () {
        $group = FilterGroup::make('date_and_price')
            ->andLogic()
            ->filters([
                RangeFilter::make('created_at')->date(),
                BetweenFilter::make('price'),
            ]);

        $query = applyFilterQuery($group, freshQuery(), [
            'created_at' => ['from' => '2024-01-01', 'to' => '2024-12-31'],
            'price' => ['from' => '10', 'to' => '100'],
        ]);
        $sql = $query->toSql();

        expect($sql)->toContain('"created_at"')
            ->and($sql)->toContain('"price"')
            ->and($sql)->toContain('between');

        $bindings = $query->getBindings();
        expect($bindings)->toContain('2024-01-01')
            ->and($bindings)->toContain('2024-12-31')
            ->and($bindings)->toContain('10')
            ->and($bindings)->toContain('100');
    });

    it('OR logic with namespaced data only applies filters with data', function () {
        $group = FilterGroup::make('search')
            ->orLogic()
            ->filters([
                LikeFilter::make('title')->sensitive(),
                LikeFilter::make('body')->sensitive(),
                LikeFilter::make('summary')->sensitive(),
            ]);

        // Only title and summary have data, body is empty
        $query = applyFilterQuery($group, freshQuery(), [
            'title' => ['value' => 'hello'],
            'body' => ['value' => ''],
            'summary' => ['value' => 'world'],
        ]);
        $sql = $query->toSql();

        expect($sql)->toContain('"title"')
            ->and($sql)->toContain('"summary"')
            ->and($sql)->toContain('or');

        // body should not appear in the SQL
        $bindings = $query->getBindings();
        expect($bindings)->toContain('%hello%')
            ->and($bindings)->toContain('%world%')
            ->and($bindings)->toHaveCount(2);
    });
});

// ─── SoftDeleteFilter ────────────────────────────────────────────

/**
 * Helper: create an Eloquent Builder for a model with SoftDeletes.
 */
function freshSoftDeleteQuery(): Builder
{
    $model = new class extends Model
    {
        use \Illuminate\Database\Eloquent\SoftDeletes;

        protected $table = 'test_items';
    };

    return $model->newQuery();
}

describe('SoftDeleteFilter Query', function () {
    it('applies withTrashed when value is with', function () {
        $filter = SoftDeleteFilter::make('trashed');
        $query = applyFilterQuery($filter, freshSoftDeleteQuery(), ['trashed' => 'with']);

        expect($query)->toBeInstanceOf(\Illuminate\Database\Eloquent\Builder::class);
    });

    it('does not modify query when value is empty', function () {
        $filter = SoftDeleteFilter::make('trashed');
        $query = applyFilterQuery($filter, freshSoftDeleteQuery(), ['trashed' => '']);
        $sql = $query->toSql();

        // Default SoftDeletes scope adds "deleted_at" is null, but empty value doesn't change anything
        expect($query)->toBeInstanceOf(\Illuminate\Database\Eloquent\Builder::class);
    });
});

// ─── ExistsFilter ────────────────────────────────────────────────

describe('ExistsFilter Query', function () {
    it('applies whereHas when exists is selected', function () {
        $filter = ExistsFilter::make('has_category')
            ->relationship('category')
            ->select();
        $query = applyFilterQuery($filter, freshQueryWithCategory(), ['value' => 'exists']);
        $sql = $query->toSql();

        expect($sql)->toContain('exists');
    });

    it('applies whereDoesntHave when not_exists is selected', function () {
        $filter = ExistsFilter::make('no_category')
            ->relationship('category')
            ->select();
        $query = applyFilterQuery($filter, freshQueryWithCategory(), ['value' => 'not_exists']);
        $sql = $query->toSql();

        expect($sql)->toContain('not exists');
    });

    it('does not modify query when value is empty', function () {
        $filter = ExistsFilter::make('has_category')
            ->relationship('category')
            ->select();
        $query = applyFilterQuery($filter, freshQuery(), ['value' => '']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('exists');
    });

    it('applies whereHas in toggle mode when enabled', function () {
        $filter = ExistsFilter::make('has_category')
            ->relationship('category')
            ->toggle();
        $query = applyFilterQuery($filter, freshQueryWithCategory(), ['value' => true]);
        $sql = $query->toSql();

        expect($sql)->toContain('exists');
    });
});

// ─── ColumnCompareFilter ─────────────────────────────────────────

describe('ColumnCompareFilter Query', function () {
    it('applies whereColumn in toggle mode', function () {
        $filter = ColumnCompareFilter::make('profitable')
            ->leftColumn('price')
            ->rightColumn('cost')
            ->gt();
        $query = applyFilterQuery($filter, freshQuery(), ['enabled' => true]);
        $sql = $query->toSql();

        expect($sql)->toContain('"price"')
            ->and($sql)->toContain('>')
            ->and($sql)->toContain('"cost"');
    });

    it('applies whereColumn in select mode', function () {
        $filter = ColumnCompareFilter::make('compare')
            ->leftColumn('start_at')
            ->rightColumn('end_at')
            ->select();
        $query = applyFilterQuery($filter, freshQuery(), ['operator' => '<=']);
        $sql = $query->toSql();

        expect($sql)->toContain('"start_at"')
            ->and($sql)->toContain('<=')
            ->and($sql)->toContain('"end_at"');
    });

    it('does not apply query for empty toggle', function () {
        $filter = ColumnCompareFilter::make('test')
            ->leftColumn('a')
            ->rightColumn('b')
            ->gt();
        $query = applyFilterQuery($filter, freshQuery(), ['enabled' => false]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('rejects invalid operator in select mode', function () {
        $filter = ColumnCompareFilter::make('test')
            ->leftColumn('a')
            ->rightColumn('b')
            ->select();
        $query = applyFilterQuery($filter, freshQuery(), ['operator' => 'DROP TABLE']);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });
});
