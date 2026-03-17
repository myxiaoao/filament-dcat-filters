<?php

use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;

it('can be instantiated', function () {
    $filter = ComparisonFilter::make('quantity');

    expect($filter)->toBeInstanceOf(ComparisonFilter::class);
});

it('has correct default column span', function () {
    $filter = ComparisonFilter::make('quantity');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Operators', function () {
    it('can set greater than operator', function () {
        $filter = ComparisonFilter::make('price')->gt();

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('can set greater than or equal operator', function () {
        $filter = ComparisonFilter::make('price')->gte();

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('can set less than operator', function () {
        $filter = ComparisonFilter::make('price')->lt();

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('can set less than or equal operator', function () {
        $filter = ComparisonFilter::make('price')->lte();

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('can set equal operator', function () {
        $filter = ComparisonFilter::make('price')->eq();

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('can set not equal operator', function () {
        $filter = ComparisonFilter::make('price')->ne();

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('can set custom operator', function () {
        $filter = ComparisonFilter::make('price')->operator('>=');

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('throws exception for invalid operator', function () {
        expect(fn () => ComparisonFilter::make('price')->operator('invalid'))
            ->toThrow(InvalidArgumentException::class);
    });
});

describe('Input Types', function () {
    it('can set integer input type', function () {
        $filter = ComparisonFilter::make('quantity')->integer();

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('can set numeric input type', function () {
        $filter = ComparisonFilter::make('price')->numeric();

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });
});

describe('Operator Labels', function () {
    it('provides human-readable operator labels', function () {
        $filter = ComparisonFilter::make('price')->gt();

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('getOperatorLabel');
        $method->setAccessible(true);

        $label = $method->invoke($filter);

        expect($label)->not->toBeEmpty();
    });
});

describe('Column Name', function () {
    it('uses filter name as column by default', function () {
        $filter = ComparisonFilter::make('quantity');

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('can set custom column name', function () {
        $filter = ComparisonFilter::make('min_price')
            ->column('price')
            ->gte();

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('can combine column with other features', function () {
        $filter = ComparisonFilter::make('stock_filter')
            ->column('stock_quantity')
            ->integer()
            ->gt();

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });
});

describe('Money Support', function () {
    it('can enable money conversion', function () {
        $filter = ComparisonFilter::make('min_price')
            ->money(100);

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('stores money divider correctly', function () {
        $filter = ComparisonFilter::make('min_price')
            ->money(100);

        $reflection = new ReflectionClass($filter);
        $prop = $reflection->getProperty('moneyDivider');
        $prop->setAccessible(true);

        expect($prop->getValue($filter))->toBe(100);
    });

    it('can set money suffix', function () {
        $filter = ComparisonFilter::make('min_price')
            ->moneySuffix('元');

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('stores money suffix correctly', function () {
        $filter = ComparisonFilter::make('min_price')
            ->moneySuffix('元');

        $reflection = new ReflectionClass($filter);
        $prop = $reflection->getProperty('moneySuffix');
        $prop->setAccessible(true);

        expect($prop->getValue($filter))->toBe('元');
    });

    it('can combine money with column and operator', function () {
        $filter = ComparisonFilter::make('min_price')
            ->column('price_cents')
            ->money(100)
            ->moneySuffix('元')
            ->gte();

        expect($filter)->toBeInstanceOf(ComparisonFilter::class);
    });

    it('uses default divider of 100', function () {
        $filter = ComparisonFilter::make('min_price')
            ->money();

        $reflection = new ReflectionClass($filter);
        $prop = $reflection->getProperty('moneyDivider');
        $prop->setAccessible(true);

        expect($prop->getValue($filter))->toBe(100);
    });
});
