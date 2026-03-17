<?php

use Cooper\FilamentDcatFilters\Filters\LikeFilter;

describe('HasInlineLabel', function () {
    it('enables inline label by default', function () {
        $filter = LikeFilter::make('test');

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('shouldInlineLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBeTrue();
    });

    it('can disable inline label', function () {
        $filter = LikeFilter::make('test')->inlineLabel(false);

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('shouldInlineLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBeFalse();
    });

    it('can enable placeholder from label', function () {
        $filter = LikeFilter::make('test')->placeholderFromLabel(true);

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('shouldPlaceholderFromLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBeTrue();
    });

    it('can disable placeholder from label', function () {
        $filter = LikeFilter::make('test')->placeholderFromLabel(false);

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('shouldPlaceholderFromLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBeFalse();
    });
});
