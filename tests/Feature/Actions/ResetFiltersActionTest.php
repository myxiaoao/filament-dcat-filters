<?php

use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;

it('can be instantiated', function () {
    $action = ResetFiltersAction::make();

    expect($action)->toBeInstanceOf(ResetFiltersAction::class);
});

it('has correct default name', function () {
    expect(ResetFiltersAction::getDefaultName())->toBe('resetFilters');
});

it('can set auto hide when empty', function () {
    $action = ResetFiltersAction::make()
        ->autoHideWhenEmpty(false);

    expect($action)->toBeInstanceOf(ResetFiltersAction::class);
});

it('can require confirmation', function () {
    $action = ResetFiltersAction::make()
        ->withConfirmation();

    expect($action)->toBeInstanceOf(ResetFiltersAction::class);
});

it('can set after reset callback', function () {
    $callbackCalled = false;

    $action = ResetFiltersAction::make()
        ->afterReset(function () use (&$callbackCalled) {
            $callbackCalled = true;
        });

    expect($action)->toBeInstanceOf(ResetFiltersAction::class);
});

it('has non-empty values correctly detects empty arrays', function () {
    $action = ResetFiltersAction::make();

    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('hasNonEmptyValues');
    $method->setAccessible(true);

    expect($method->invoke($action, []))->toBeFalse();
    expect($method->invoke($action, ['key' => null]))->toBeFalse();
    expect($method->invoke($action, ['key' => '']))->toBeFalse();
    expect($method->invoke($action, ['key' => []]))->toBeFalse();
});

it('has non-empty values correctly detects non-empty arrays', function () {
    $action = ResetFiltersAction::make();

    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('hasNonEmptyValues');
    $method->setAccessible(true);

    expect($method->invoke($action, ['key' => 'value']))->toBeTrue();
    expect($method->invoke($action, ['key' => 0]))->toBeTrue();
    expect($method->invoke($action, ['key' => false]))->toBeTrue();
    expect($method->invoke($action, ['nested' => ['key' => 'value']]))->toBeTrue();
});

it('has non-empty values handles nested arrays', function () {
    $action = ResetFiltersAction::make();

    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('hasNonEmptyValues');
    $method->setAccessible(true);

    // Nested empty arrays
    expect($method->invoke($action, ['level1' => ['level2' => null]]))->toBeFalse();
    expect($method->invoke($action, ['level1' => ['level2' => '']]))->toBeFalse();

    // Nested non-empty arrays
    expect($method->invoke($action, ['level1' => ['level2' => 'value']]))->toBeTrue();
});

it('resolves LocalStorage key from livewire component', function () {
    $action = ResetFiltersAction::make();

    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('resolveLocalStorageKey');
    $method->setAccessible(true);

    // Component with getLocalStorageKey method
    $livewire = new class
    {
        public function getLocalStorageKey(): string
        {
            return 'filament-dcat-filters:TestComponent';
        }
    };

    expect($method->invoke($action, $livewire))->toBe('filament-dcat-filters:TestComponent');
});

it('returns null when livewire component has no LocalStorage key method', function () {
    $action = ResetFiltersAction::make();

    $reflection = new ReflectionClass($action);
    $method = $reflection->getMethod('resolveLocalStorageKey');
    $method->setAccessible(true);

    $livewire = new class {};

    expect($method->invoke($action, $livewire))->toBeNull();
});
