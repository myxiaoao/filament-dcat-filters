<?php

use Cooper\FilamentDcatFilters\Facades\FilamentDcatFilters;
use Cooper\FilamentDcatFilters\Filters;

it('facade resolves to correct instance', function () {
    $instance = FilamentDcatFilters::getFacadeRoot();
    expect($instance)->toBeInstanceOf(Cooper\FilamentDcatFilters\FilamentDcatFilters::class);
});

it('facade creates scope filter', function () {
    $filter = FilamentDcatFilters::scopeFilter('status');
    expect($filter)->toBeInstanceOf(Filters\ScopeFilter::class);
});

it('facade creates range filter', function () {
    $filter = FilamentDcatFilters::rangeFilter('created_at');
    expect($filter)->toBeInstanceOf(Filters\RangeFilter::class);
});

it('facade creates like filter', function () {
    $filter = FilamentDcatFilters::likeFilter('title');
    expect($filter)->toBeInstanceOf(Filters\LikeFilter::class);
});

it('facade creates boolean filter', function () {
    $filter = FilamentDcatFilters::booleanFilter('is_active');
    expect($filter)->toBeInstanceOf(Filters\BooleanFilter::class);
});

it('returns version string', function () {
    $version = FilamentDcatFilters::version();
    expect($version)->toBeString()->not->toBeEmpty();
});
