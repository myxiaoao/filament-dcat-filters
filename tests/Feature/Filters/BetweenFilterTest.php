<?php

use Cooper\FilamentDcatFilters\Filters\BetweenFilter;
use Cooper\FilamentDcatFilters\Filters\RangeFilter;

it('can be instantiated', function () {
    $filter = BetweenFilter::make('price');

    expect($filter)->toBeInstanceOf(BetweenFilter::class);
});

it('extends RangeFilter', function () {
    $filter = BetweenFilter::make('price');

    expect($filter)->toBeInstanceOf(RangeFilter::class);
});

it('has correct default column span', function () {
    $filter = BetweenFilter::make('price');

    expect($filter->getColumnSpan())->toBe(1);
});

it('defaults to integer type', function () {
    $filter = BetweenFilter::make('quantity');

    $reflection = new ReflectionClass($filter);
    $property = $reflection->getProperty('rangeType');
    $property->setAccessible(true);

    expect($property->getValue($filter))->toBe('integer');
});

describe('Type Methods', function () {
    it('can set numeric type', function () {
        $filter = BetweenFilter::make('price')->numeric();

        $reflection = new ReflectionClass($filter);
        $property = $reflection->getProperty('rangeType');
        $property->setAccessible(true);

        expect($property->getValue($filter))->toBe('numeric');
    });

    it('can set integer type', function () {
        $filter = BetweenFilter::make('quantity')->integer();

        $reflection = new ReflectionClass($filter);
        $property = $reflection->getProperty('rangeType');
        $property->setAccessible(true);

        expect($property->getValue($filter))->toBe('integer');
    });
});

describe('Label', function () {
    it('can set label', function () {
        $filter = BetweenFilter::make('price')->label('Price Range');

        expect($filter)->toBeInstanceOf(BetweenFilter::class);
        expect($filter->getLabel())->toBe('Price Range');
    });
});
