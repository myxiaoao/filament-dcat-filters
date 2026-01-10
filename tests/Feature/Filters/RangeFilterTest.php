<?php

use Cooper\FilamentDcatFilters\Filters\RangeFilter;

it('can be instantiated', function () {
    $filter = RangeFilter::make('price');

    expect($filter)->toBeInstanceOf(RangeFilter::class);
});

it('has correct default column span', function () {
    $filter = RangeFilter::make('price');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Numeric Range', function () {
    it('can create numeric range filter', function () {
        $filter = RangeFilter::make('price')->numeric();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can create integer range filter', function () {
        $filter = RangeFilter::make('quantity')->integer();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });
});

describe('Date Range', function () {
    it('can create date range filter', function () {
        $filter = RangeFilter::make('created_at')->date();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can create datetime range filter', function () {
        $filter = RangeFilter::make('created_at')->datetime();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can create time range filter', function () {
        $filter = RangeFilter::make('start_time')->time();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can set custom format', function () {
        $filter = RangeFilter::make('created_at')
            ->date()
            ->format('d/m/Y');

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });
});

describe('Placeholders', function () {
    it('can set placeholders with array', function () {
        $filter = RangeFilter::make('price')
            ->placeholders(['from' => 'Min', 'to' => 'Max'])
            ->numeric();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can set placeholders with two strings', function () {
        $filter = RangeFilter::make('price')
            ->placeholders('Min Price', 'Max Price')
            ->numeric();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });
});

describe('Timestamp Conversion', function () {
    it('can convert to timestamp', function () {
        $filter = RangeFilter::make('created_at')
            ->datetime()
            ->toTimestamp();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });
});
