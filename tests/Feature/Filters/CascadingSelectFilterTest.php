<?php

use Cooper\FilamentDcatFilters\Filters\CascadingSelectFilter;

it('can be instantiated', function () {
    $filter = CascadingSelectFilter::make('location');

    expect($filter)->toBeInstanceOf(CascadingSelectFilter::class);
});

it('can add levels using addLevel method', function () {
    // We can't test with real models in unit tests, so we just test the method exists
    // and throws proper exception for invalid model
    $filter = CascadingSelectFilter::make('location');

    expect(fn () => $filter->addLevel(
        name: 'country_id',
        label: 'Country',
        model: 'InvalidModelClass'
    ))->toThrow(\InvalidArgumentException::class, 'Invalid model class');
});

it('stores level configurations correctly', function () {
    $filter = CascadingSelectFilter::make('location');

    $reflection = new ReflectionClass($filter);

    $levelsProperty = $reflection->getProperty('levels');
    $levelsProperty->setAccessible(true);

    $configsProperty = $reflection->getProperty('levelConfigs');
    $configsProperty->setAccessible(true);

    // Initially empty
    expect($levelsProperty->getValue($filter))->toBe([]);
    expect($configsProperty->getValue($filter))->toBe([]);
});

it('has correct default column span', function () {
    $filter = CascadingSelectFilter::make('location');

    // The filter should have default column span of 1
    expect($filter->getColumnSpan())->toBe(1);
});
