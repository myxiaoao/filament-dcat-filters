<?php

use Cooper\FilamentDcatFilters\Filters\BetweenFilter;
use Cooper\FilamentDcatFilters\Filters\BooleanFilter;
use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;
use Cooper\FilamentDcatFilters\Filters\HiddenFilter;
use Cooper\FilamentDcatFilters\Filters\InFilter;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;
use Cooper\FilamentDcatFilters\Filters\NullFilter;
use Cooper\FilamentDcatFilters\Filters\RangeFilter;
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
