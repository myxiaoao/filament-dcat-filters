<?php

use Cooper\FilamentDcatFilters\Filters\HiddenFilter;

it('can be instantiated', function () {
    $filter = HiddenFilter::make('tenant_id');

    expect($filter)->toBeInstanceOf(HiddenFilter::class);
});

it('has correct default column span', function () {
    $filter = HiddenFilter::make('tenant_id');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Default Value', function () {
    it('can set default value', function () {
        $filter = HiddenFilter::make('tenant_id')->default(1);

        expect($filter)->toBeInstanceOf(HiddenFilter::class);
    });

    it('can set string default value', function () {
        $filter = HiddenFilter::make('type')->default('admin');

        expect($filter)->toBeInstanceOf(HiddenFilter::class);
    });

    it('can set boolean default value', function () {
        $filter = HiddenFilter::make('is_active')->default(true);

        expect($filter)->toBeInstanceOf(HiddenFilter::class);
    });
});

describe('Operators', function () {
    it('can set greater than operator', function () {
        $filter = HiddenFilter::make('level')->default(5)->gt();

        expect($filter)->toBeInstanceOf(HiddenFilter::class);
    });

    it('can set greater than or equal operator', function () {
        $filter = HiddenFilter::make('level')->default(5)->gte();

        expect($filter)->toBeInstanceOf(HiddenFilter::class);
    });

    it('can set less than operator', function () {
        $filter = HiddenFilter::make('level')->default(5)->lt();

        expect($filter)->toBeInstanceOf(HiddenFilter::class);
    });

    it('can set less than or equal operator', function () {
        $filter = HiddenFilter::make('level')->default(5)->lte();

        expect($filter)->toBeInstanceOf(HiddenFilter::class);
    });

    it('can set equal operator', function () {
        $filter = HiddenFilter::make('status')->default('active')->eq();

        expect($filter)->toBeInstanceOf(HiddenFilter::class);
    });

    it('can set not equal operator', function () {
        $filter = HiddenFilter::make('status')->default('deleted')->ne();

        expect($filter)->toBeInstanceOf(HiddenFilter::class);
    });

    it('can set custom operator', function () {
        $filter = HiddenFilter::make('name')->default('%test%')->operator('like');

        expect($filter)->toBeInstanceOf(HiddenFilter::class);
    });

    it('throws exception for invalid operator', function () {
        expect(fn () => HiddenFilter::make('field')->operator('invalid'))
            ->toThrow(\InvalidArgumentException::class);
    });
});

describe('Allowed Operators', function () {
    it('allows all valid operators', function () {
        $allowedOperators = ['=', '!=', '>', '>=', '<', '<=', 'like', 'not like'];

        foreach ($allowedOperators as $operator) {
            $filter = HiddenFilter::make('field')->operator($operator);
            expect($filter)->toBeInstanceOf(HiddenFilter::class);
        }
    });
});
