<?php

use Cooper\FilamentDcatFilters\Filters\DateComponentFilter;

it('can be instantiated', function () {
    $filter = DateComponentFilter::make('created_at');

    expect($filter)->toBeInstanceOf(DateComponentFilter::class);
});

it('has correct default column span', function () {
    $filter = DateComponentFilter::make('created_at');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Year Filter', function () {
    it('can create year filter', function () {
        $filter = DateComponentFilter::make('created_at')->year();

        expect($filter)->toBeInstanceOf(DateComponentFilter::class);
    });

    it('sets component to year', function () {
        $filter = DateComponentFilter::make('created_at')->year();

        $reflection = new ReflectionClass($filter);
        $property = $reflection->getProperty('component');
        $property->setAccessible(true);

        expect($property->getValue($filter))->toBe('year');
    });
});

describe('Month Filter', function () {
    it('can create month filter', function () {
        $filter = DateComponentFilter::make('created_at')->month();

        expect($filter)->toBeInstanceOf(DateComponentFilter::class);
    });

    it('sets component to month', function () {
        $filter = DateComponentFilter::make('created_at')->month();

        $reflection = new ReflectionClass($filter);
        $property = $reflection->getProperty('component');
        $property->setAccessible(true);

        expect($property->getValue($filter))->toBe('month');
    });
});

describe('Day Filter', function () {
    it('can create day filter', function () {
        $filter = DateComponentFilter::make('created_at')->day();

        expect($filter)->toBeInstanceOf(DateComponentFilter::class);
    });

    it('sets component to day', function () {
        $filter = DateComponentFilter::make('created_at')->day();

        $reflection = new ReflectionClass($filter);
        $property = $reflection->getProperty('component');
        $property->setAccessible(true);

        expect($property->getValue($filter))->toBe('day');
    });
});

describe('SQL Function Validation', function () {
    it('only allows valid SQL functions', function () {
        $filter = DateComponentFilter::make('created_at');

        // These should work without throwing exceptions
        $filter->year();
        $filter->month();
        $filter->day();

        expect(true)->toBeTrue(); // If we get here, no exceptions were thrown
    });

    it('throws exception for invalid SQL function', function () {
        $filter = DateComponentFilter::make('created_at');

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('configureQuery');
        $method->setAccessible(true);

        expect(fn () => $method->invoke($filter, 'INVALID'))
            ->toThrow(\InvalidArgumentException::class, 'Invalid SQL function');
    });
});
