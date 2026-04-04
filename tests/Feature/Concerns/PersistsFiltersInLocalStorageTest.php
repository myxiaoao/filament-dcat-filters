<?php

use Cooper\FilamentDcatFilters\Concerns\PersistsFiltersInLocalStorage;
use Livewire\Attributes\On;

// Create a mock class that uses the trait
class MockLocalStorageComponent
{
    use PersistsFiltersInLocalStorage;

    public array $tableFilters = [];

    protected ?string $id = 'test-component-123';

    public function getId(): ?string
    {
        return $this->id;
    }

    public array $dispatched = [];

    public function dispatch(string $event, array $params = []): void
    {
        $this->dispatched[] = ['event' => $event, 'params' => $params];
    }
}

it('generates valid LocalStorage key', function () {
    $component = new MockLocalStorageComponent;

    $reflection = new ReflectionClass($component);
    $method = $reflection->getMethod('getLocalStorageKey');
    $method->setAccessible(true);

    $key = $method->invoke($component);

    expect($key)->toContain('filament-dcat-filters:');
    expect($key)->not->toContain('\\');
});

it('dispatches init event on initialization', function () {
    $component = new MockLocalStorageComponent;

    $reflection = new ReflectionClass($component);
    $method = $reflection->getMethod('initLocalStoragePersistence');
    $method->setAccessible(true);
    $method->invoke($component);

    expect($component->dispatched)->toHaveCount(1);
    expect($component->dispatched[0]['event'])->toBe('filament-dcat-filters::init-local-storage');
    expect($component->dispatched[0]['params'])->toHaveKey('key');
    expect($component->dispatched[0]['params'])->toHaveKey('componentId');
});

it('dispatches restore event on mount', function () {
    $component = new MockLocalStorageComponent;

    $component->mountPersistsFiltersInLocalStorage();

    expect($component->dispatched)->toHaveCount(1);
    expect($component->dispatched[0]['event'])->toBe('filament-dcat-filters::restore-from-local-storage');
});

it('restores filters from callback', function () {
    $component = new MockLocalStorageComponent;

    $filters = ['status' => ['value' => 'active']];
    $component->restoreFiltersFromLocalStorage($filters);

    expect($component->tableFilters)->toBe($filters);
});

it('does not restore empty filters', function () {
    $component = new MockLocalStorageComponent;
    $component->tableFilters = ['existing' => ['value' => 'data']];

    $component->restoreFiltersFromLocalStorage([]);

    expect($component->tableFilters)->toBe(['existing' => ['value' => 'data']]);
});

it('has #[On] attribute registered for restoreFiltersFromLocalStorage', function () {
    $reflection = new ReflectionMethod(MockLocalStorageComponent::class, 'restoreFiltersFromLocalStorage');
    $attributes = $reflection->getAttributes(On::class);

    expect($attributes)->toHaveCount(1);
    expect($attributes[0]->newInstance()->event)->toBe('restoreFiltersFromLocalStorage');
});
