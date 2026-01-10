<?php

use Cooper\FilamentDcatFilters\Concerns\PersistsFiltersInSession;
use Illuminate\Support\Facades\Session;

// Create a mock class that uses the trait
class MockSessionComponent
{
    use PersistsFiltersInSession;

    public array $tableFilters = [];
}

it('generates unique session key per class', function () {
    $component = new MockSessionComponent;

    $reflection = new ReflectionClass($component);
    $method = $reflection->getMethod('getFilterSessionKey');
    $method->setAccessible(true);

    $key = $method->invoke($component);

    expect($key)->toContain('filament-dcat-filters:');
    expect($key)->toContain('MockSessionComponent');
});

it('saves filters to session', function () {
    Session::flush();

    $component = new MockSessionComponent;
    $component->tableFilters = ['status' => ['value' => 'active']];

    $reflection = new ReflectionClass($component);

    $keyMethod = $reflection->getMethod('getFilterSessionKey');
    $keyMethod->setAccessible(true);
    $key = $keyMethod->invoke($component);

    $saveMethod = $reflection->getMethod('saveFiltersToSession');
    $saveMethod->setAccessible(true);
    $saveMethod->invoke($component);

    expect(Session::get($key))->toBe(['status' => ['value' => 'active']]);
});

it('restores filters from session', function () {
    Session::flush();

    $component = new MockSessionComponent;

    $reflection = new ReflectionClass($component);

    $keyMethod = $reflection->getMethod('getFilterSessionKey');
    $keyMethod->setAccessible(true);
    $key = $keyMethod->invoke($component);

    // Pre-set session data
    Session::put($key, ['category' => ['value' => 'news']]);

    $restoreMethod = $reflection->getMethod('restoreFiltersFromSession');
    $restoreMethod->setAccessible(true);
    $restoreMethod->invoke($component);

    expect($component->tableFilters)->toBe(['category' => ['value' => 'news']]);
});

it('clears filters from session', function () {
    Session::flush();

    $component = new MockSessionComponent;
    $component->tableFilters = ['status' => ['value' => 'active']];

    $reflection = new ReflectionClass($component);

    $keyMethod = $reflection->getMethod('getFilterSessionKey');
    $keyMethod->setAccessible(true);
    $key = $keyMethod->invoke($component);

    // Save first
    $saveMethod = $reflection->getMethod('saveFiltersToSession');
    $saveMethod->setAccessible(true);
    $saveMethod->invoke($component);

    expect(Session::has($key))->toBeTrue();

    // Then clear
    $clearMethod = $reflection->getMethod('clearFiltersFromSession');
    $clearMethod->setAccessible(true);
    $clearMethod->invoke($component);

    expect(Session::has($key))->toBeFalse();
});

it('does not restore empty session data', function () {
    Session::flush();

    $component = new MockSessionComponent;
    $component->tableFilters = ['existing' => ['value' => 'data']];

    $reflection = new ReflectionClass($component);

    $restoreMethod = $reflection->getMethod('restoreFiltersFromSession');
    $restoreMethod->setAccessible(true);
    $restoreMethod->invoke($component);

    // Original data should remain unchanged
    expect($component->tableFilters)->toBe(['existing' => ['value' => 'data']]);
});
