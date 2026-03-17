<?php

use Cooper\FilamentDcatFilters\Filters\BooleanFilter;

describe('HasLabelResolver', function () {
    it('resolves label from filter name by default', function () {
        $filter = BooleanFilter::make('is_active');

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('resolveLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBe('Is active');
    });

    it('resolves label from custom label when set', function () {
        $filter = BooleanFilter::make('is_active')->label('Status');

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('resolveLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBe('Status');
    });

    it('returns a closure from labelResolver', function () {
        $filter = BooleanFilter::make('test');

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('labelResolver');
        $method->setAccessible(true);

        $resolver = $method->invoke($filter);

        expect($resolver)->toBeInstanceOf(Closure::class);
        expect($resolver())->toBeString();
    });
});
