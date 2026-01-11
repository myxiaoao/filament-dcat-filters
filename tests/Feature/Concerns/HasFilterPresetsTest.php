<?php

use Cooper\FilamentDcatFilters\Concerns\HasFilterPresets;

// Create a test class that uses the trait with public methods for testing
class TestListRecordsWithPresets
{
    use HasFilterPresets {
        getFilterPresetActions as public;
        applyFilterPreset as public;
        resetFilterPresets as public;
        isFilterPresetActive as public;
        getActiveFilterPreset as public;
    }

    public array $tableFilters = [];

    protected bool $pageReset = false;

    public function getFilterPresets(): array
    {
        return [
            'pending_orders' => [
                'label' => 'Pending Orders',
                'filters' => ['status' => 'pending', 'payment' => 'unpaid'],
            ],
            'high_value' => [
                'label' => 'High Value Orders',
                'filters' => ['total' => ['from' => 1000]],
                'icon' => 'heroicon-o-currency-dollar',
                'color' => 'success',
            ],
            'recent' => [
                'label' => 'Recent Orders',
                'filters' => ['date_range' => 'last_7_days'],
            ],
        ];
    }

    public function resetPage(): void
    {
        $this->pageReset = true;
    }

    public function wasPageReset(): bool
    {
        return $this->pageReset;
    }
}

beforeEach(function () {
    $this->listRecords = new TestListRecordsWithPresets;
});

it('can get filter presets', function () {
    $presets = $this->listRecords->getFilterPresets();

    expect($presets)->toHaveCount(3);
    expect($presets)->toHaveKeys(['pending_orders', 'high_value', 'recent']);
});

it('can get filter preset actions', function () {
    $actions = $this->listRecords->getFilterPresetActions();

    expect($actions)->toHaveCount(3);
});

it('can apply a filter preset with simple values', function () {
    $this->listRecords->applyFilterPreset(['status' => 'pending', 'payment' => 'unpaid']);

    expect($this->listRecords->tableFilters)->toBe([
        'status' => ['value' => 'pending'],
        'payment' => ['value' => 'unpaid'],
    ]);
    expect($this->listRecords->wasPageReset())->toBeTrue();
});

it('can apply a filter preset with array values', function () {
    $this->listRecords->applyFilterPreset(['total' => ['from' => 1000]]);

    expect($this->listRecords->tableFilters)->toBe([
        'total' => ['from' => 1000],
    ]);
});

it('can reset filter presets', function () {
    $this->listRecords->tableFilters = ['status' => ['value' => 'pending']];

    $this->listRecords->resetFilterPresets();

    expect($this->listRecords->tableFilters)->toBe([]);
    expect($this->listRecords->wasPageReset())->toBeTrue();
});

it('can check if a preset is active', function () {
    $this->listRecords->tableFilters = [
        'status' => ['value' => 'pending'],
        'payment' => ['value' => 'unpaid'],
    ];

    expect($this->listRecords->isFilterPresetActive('pending_orders'))->toBeTrue();
    expect($this->listRecords->isFilterPresetActive('high_value'))->toBeFalse();
    expect($this->listRecords->isFilterPresetActive('recent'))->toBeFalse();
});

it('can check if a preset with array values is active', function () {
    $this->listRecords->tableFilters = [
        'total' => ['from' => 1000],
    ];

    expect($this->listRecords->isFilterPresetActive('high_value'))->toBeTrue();
    expect($this->listRecords->isFilterPresetActive('pending_orders'))->toBeFalse();
});

it('returns false for non-existent preset', function () {
    expect($this->listRecords->isFilterPresetActive('non_existent'))->toBeFalse();
});

it('can get the active preset key', function () {
    $this->listRecords->tableFilters = [
        'date_range' => ['value' => 'last_7_days'],
    ];

    expect($this->listRecords->getActiveFilterPreset())->toBe('recent');
});

it('returns null when no preset is active', function () {
    $this->listRecords->tableFilters = [
        'some_other_filter' => ['value' => 'test'],
    ];

    expect($this->listRecords->getActiveFilterPreset())->toBeNull();
});

// Test class with empty presets
class TestListRecordsWithoutPresets
{
    use HasFilterPresets {
        getFilterPresetActions as public;
    }

    public array $tableFilters = [];

    public function resetPage(): void {}
}

it('returns empty actions when no presets defined', function () {
    $listRecords = new TestListRecordsWithoutPresets;

    $actions = $listRecords->getFilterPresetActions();

    expect($actions)->toBe([]);
});
