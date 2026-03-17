<?php

use Cooper\FilamentDcatFilters\Filters\BetweenFilter;
use Cooper\FilamentDcatFilters\Filters\BooleanFilter;
use Cooper\FilamentDcatFilters\Filters\CascadingSelectFilter;
use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;
use Cooper\FilamentDcatFilters\Filters\DateComponentFilter;
use Cooper\FilamentDcatFilters\Filters\EnumFilter;
use Cooper\FilamentDcatFilters\Filters\FilterGroup;
use Cooper\FilamentDcatFilters\Filters\FindInSetFilter;
use Cooper\FilamentDcatFilters\Filters\FullTextFilter;
use Cooper\FilamentDcatFilters\Filters\GeoLocationFilter;
use Cooper\FilamentDcatFilters\Filters\HiddenFilter;
use Cooper\FilamentDcatFilters\Filters\InFilter;
use Cooper\FilamentDcatFilters\Filters\InputMaskFilter;
use Cooper\FilamentDcatFilters\Filters\JsonFilter;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;
use Cooper\FilamentDcatFilters\Filters\ModalSelectFilter;
use Cooper\FilamentDcatFilters\Filters\NullFilter;
use Cooper\FilamentDcatFilters\Filters\RangeFilter;
use Cooper\FilamentDcatFilters\Filters\RegexFilter;
use Cooper\FilamentDcatFilters\Filters\RelativeDateFilter;
use Cooper\FilamentDcatFilters\Filters\ScopeFilter;
use Cooper\FilamentDcatFilters\Filters\SelectTableFilter;

describe('Filter Smoke Tests', function () {
    it('can instantiate all filter types', function (string $class) {
        $filter = $class::make('test');
        expect($filter)->toBeInstanceOf($class);
    })->with([
        'BetweenFilter' => BetweenFilter::class,
        'BooleanFilter' => BooleanFilter::class,
        'CascadingSelectFilter' => CascadingSelectFilter::class,
        'ComparisonFilter' => ComparisonFilter::class,
        'DateComponentFilter' => DateComponentFilter::class,
        'EnumFilter' => EnumFilter::class,
        'FilterGroup' => FilterGroup::class,
        'FindInSetFilter' => FindInSetFilter::class,
        'FullTextFilter' => FullTextFilter::class,
        'GeoLocationFilter' => GeoLocationFilter::class,
        'HiddenFilter' => HiddenFilter::class,
        'InFilter' => InFilter::class,
        'InputMaskFilter' => InputMaskFilter::class,
        'JsonFilter' => JsonFilter::class,
        'LikeFilter' => LikeFilter::class,
        'ModalSelectFilter' => ModalSelectFilter::class,
        'NullFilter' => NullFilter::class,
        'RangeFilter' => RangeFilter::class,
        'RegexFilter' => RegexFilter::class,
        'RelativeDateFilter' => RelativeDateFilter::class,
        'ScopeFilter' => ScopeFilter::class,
        'SelectTableFilter' => SelectTableFilter::class,
    ]);

    it('all filters have default column span of 1 or 2', function (string $class) {
        $filter = $class::make('test');
        expect($filter->getColumnSpan())->toBeIn([1, 2]);
    })->with([
        'BetweenFilter' => BetweenFilter::class,
        'BooleanFilter' => BooleanFilter::class,
        'ComparisonFilter' => ComparisonFilter::class,
        'EnumFilter' => EnumFilter::class,
        'FullTextFilter' => FullTextFilter::class,
        'GeoLocationFilter' => GeoLocationFilter::class,
        'HiddenFilter' => HiddenFilter::class,
        'InFilter' => InFilter::class,
        'LikeFilter' => LikeFilter::class,
        'NullFilter' => NullFilter::class,
        'RangeFilter' => RangeFilter::class,
        'RegexFilter' => RegexFilter::class,
        'SelectTableFilter' => SelectTableFilter::class,
    ]);
});
