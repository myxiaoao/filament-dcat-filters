<?php

use Cooper\FilamentDcatFilters\Concerns\HasRangeQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

// Create a test class that uses the trait
class HasRangeQueryTestClass
{
    use HasRangeQuery;

    public function testIsRangeValueEmpty(mixed $value): bool
    {
        return $this->isRangeValueEmpty($value);
    }

    public function testGenerateRangeIndicators(array $data, string $label): array
    {
        return $this->generateRangeIndicators($data, $label);
    }

    public function testApplyRangeQuery(Builder $query, string $column, array $data): Builder
    {
        return $this->applyRangeQuery($query, $column, $data);
    }
}

function freshRangeQuery(): Builder
{
    $model = new class extends Model
    {
        protected $table = 'test_items';
    };

    return $model->newQuery();
}

beforeEach(function () {
    $this->testClass = new HasRangeQueryTestClass;
});

describe('isRangeValueEmpty', function () {
    it('returns true for null', function () {
        expect($this->testClass->testIsRangeValueEmpty(null))->toBeTrue();
    });

    it('returns true for empty string', function () {
        expect($this->testClass->testIsRangeValueEmpty(''))->toBeTrue();
    });

    it('returns false for zero', function () {
        expect($this->testClass->testIsRangeValueEmpty(0))->toBeFalse();
    });

    it('returns false for string zero', function () {
        expect($this->testClass->testIsRangeValueEmpty('0'))->toBeFalse();
    });

    it('returns false for non-empty values', function () {
        expect($this->testClass->testIsRangeValueEmpty(1))->toBeFalse();
        expect($this->testClass->testIsRangeValueEmpty('value'))->toBeFalse();
        expect($this->testClass->testIsRangeValueEmpty(1.5))->toBeFalse();
    });
});

describe('generateRangeIndicators', function () {
    it('returns empty array when both from and to are empty', function () {
        $indicators = $this->testClass->testGenerateRangeIndicators(
            ['from' => null, 'to' => null],
            'Price'
        );

        expect($indicators)->toBe([]);
    });

    it('returns from indicator when only from is set', function () {
        $indicators = $this->testClass->testGenerateRangeIndicators(
            ['from' => 100, 'to' => null],
            'Price'
        );

        expect($indicators)->toHaveCount(1);
        expect($indicators[0])->toContain('Price');
        expect($indicators[0])->toContain('100');
    });

    it('returns to indicator when only to is set', function () {
        $indicators = $this->testClass->testGenerateRangeIndicators(
            ['from' => null, 'to' => 200],
            'Price'
        );

        expect($indicators)->toHaveCount(1);
        expect($indicators[0])->toContain('Price');
        expect($indicators[0])->toContain('200');
    });

    it('returns both indicators when both are set', function () {
        $indicators = $this->testClass->testGenerateRangeIndicators(
            ['from' => 100, 'to' => 200],
            'Price'
        );

        expect($indicators)->toHaveCount(2);
        expect($indicators[0])->toContain('100');
        expect($indicators[1])->toContain('200');
    });

    it('handles string values', function () {
        $indicators = $this->testClass->testGenerateRangeIndicators(
            ['from' => '2024-01-01', 'to' => '2024-12-31'],
            'Date'
        );

        expect($indicators)->toHaveCount(2);
        expect($indicators[0])->toContain('2024-01-01');
        expect($indicators[1])->toContain('2024-12-31');
    });
});

describe('applyRangeQuery', function () {
    it('applies whereBetween when both from and to are provided', function () {
        $query = $this->testClass->testApplyRangeQuery(freshRangeQuery(), 'price', ['from' => 10, 'to' => 100]);
        $sql = $query->toSql();

        expect($sql)->toContain('between');
        expect($query->getBindings())->toContain(10);
        expect($query->getBindings())->toContain(100);
    });

    it('applies >= when only from is provided', function () {
        $query = $this->testClass->testApplyRangeQuery(freshRangeQuery(), 'price', ['from' => 50, 'to' => null]);
        $sql = $query->toSql();

        expect($sql)->toContain('>=');
        expect($query->getBindings())->toContain(50);
    });

    it('applies <= when only to is provided', function () {
        $query = $this->testClass->testApplyRangeQuery(freshRangeQuery(), 'price', ['from' => null, 'to' => 200]);
        $sql = $query->toSql();

        expect($sql)->toContain('<=');
        expect($query->getBindings())->toContain(200);
    });

    it('adds no where clause when both from and to are empty', function () {
        $query = $this->testClass->testApplyRangeQuery(freshRangeQuery(), 'price', ['from' => null, 'to' => null]);
        $sql = $query->toSql();

        expect($sql)->not->toContain('where');
    });

    it('swaps from and to when from is greater than to', function () {
        $query = $this->testClass->testApplyRangeQuery(freshRangeQuery(), 'price', ['from' => 200, 'to' => 10]);
        $bindings = $query->getBindings();

        // After swap: between 10 and 200
        expect($bindings[0])->toBe(10);
        expect($bindings[1])->toBe(200);
    });

    it('treats zero as a valid non-empty value', function () {
        $query = $this->testClass->testApplyRangeQuery(freshRangeQuery(), 'price', ['from' => 0, 'to' => null]);
        $sql = $query->toSql();

        expect($sql)->toContain('>=');
        expect($query->getBindings())->toContain(0);
    });
});
