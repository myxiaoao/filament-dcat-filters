<?php

use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;

it('can set valid operators via shortcuts', function (string $method, string $expected) {
    $filter = ComparisonFilter::make('price')->{$method}();
    $prop = new ReflectionProperty($filter, 'operator');
    expect($prop->getValue($filter))->toBe($expected);
})->with([
    ['gt', '>'],
    ['gte', '>='],
    ['lt', '<'],
    ['lte', '<='],
    ['eq', '='],
    ['ne', '!='],
]);

it('throws on invalid operator', function () {
    ComparisonFilter::make('price')->operator('INVALID');
})->throws(\InvalidArgumentException::class);

it('operator returns static for chaining', function () {
    $filter = ComparisonFilter::make('price');
    $result = $filter->gt();
    expect($result)->toBe($filter);
});
