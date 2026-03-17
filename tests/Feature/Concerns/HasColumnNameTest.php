<?php

use Cooper\FilamentDcatFilters\Filters\LikeFilter;

it('uses filter name as column by default', function () {
    $filter = LikeFilter::make('email');
    $method = new ReflectionMethod($filter, 'resolveColumnName');
    expect($method->invoke($filter))->toBe('email');
});

it('can set custom column name', function () {
    $filter = LikeFilter::make('search')->column('user_email');
    $method = new ReflectionMethod($filter, 'resolveColumnName');
    expect($method->invoke($filter))->toBe('user_email');
});

it('column method returns static for chaining', function () {
    $filter = LikeFilter::make('test');
    $result = $filter->column('custom_column');
    expect($result)->toBe($filter);
});
