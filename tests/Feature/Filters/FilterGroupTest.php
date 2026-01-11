<?php

use Cooper\FilamentDcatFilters\Filters\FilterGroup;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;

it('can be instantiated', function () {
    $filter = FilterGroup::make('search_group');

    expect($filter)->toBeInstanceOf(FilterGroup::class);
});

it('has correct default column span', function () {
    $filter = FilterGroup::make('search_group');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Logic', function () {
    it('defaults to AND logic', function () {
        $filter = FilterGroup::make('search_group');

        expect($filter->getLogic())->toBe('and');
    });

    it('can set OR logic', function () {
        $filter = FilterGroup::make('search_group')
            ->logic('or');

        expect($filter->getLogic())->toBe('or');
    });

    it('can use orLogic helper', function () {
        $filter = FilterGroup::make('search_group')
            ->orLogic();

        expect($filter->getLogic())->toBe('or');
    });

    it('can use andLogic helper', function () {
        $filter = FilterGroup::make('search_group')
            ->orLogic()
            ->andLogic();

        expect($filter->getLogic())->toBe('and');
    });

    it('normalizes logic to lowercase', function () {
        $filter = FilterGroup::make('search_group')
            ->logic('OR');

        expect($filter->getLogic())->toBe('or');
    });

    it('defaults to AND for invalid logic', function () {
        $filter = FilterGroup::make('search_group')
            ->logic('invalid');

        expect($filter->getLogic())->toBe('and');
    });
});

describe('Child Filters', function () {
    it('can set child filters', function () {
        $filter = FilterGroup::make('search_group')
            ->filters([
                LikeFilter::make('title'),
                LikeFilter::make('description'),
            ]);

        expect($filter->getChildFilters())->toHaveCount(2);
    });

    it('returns empty array when no filters set', function () {
        $filter = FilterGroup::make('search_group');

        expect($filter->getChildFilters())->toBe([]);
    });
});

describe('Combined Features', function () {
    it('can combine filters with OR logic', function () {
        $filter = FilterGroup::make('content_search')
            ->orLogic()
            ->filters([
                LikeFilter::make('title'),
                LikeFilter::make('description'),
                LikeFilter::make('content'),
            ]);

        expect($filter->getLogic())->toBe('or');
        expect($filter->getChildFilters())->toHaveCount(3);
    });

    it('can combine filters with AND logic', function () {
        $filter = FilterGroup::make('content_search')
            ->andLogic()
            ->filters([
                LikeFilter::make('title'),
                LikeFilter::make('author'),
            ]);

        expect($filter->getLogic())->toBe('and');
        expect($filter->getChildFilters())->toHaveCount(2);
    });
});
