<?php

use Cooper\FilamentDcatFilters\Concerns\HasSortPresets;

// Create a test class that uses the trait
beforeEach(function () {
    $this->sortable = new class
    {
        use HasSortPresets;

        public ?string $tableSortColumn = null;

        public ?string $tableSortDirection = null;

        protected function getSortPresets(): array
        {
            return [
                'newest' => ['column' => 'created_at', 'direction' => 'desc', 'label' => 'Newest First'],
                'oldest' => ['column' => 'created_at', 'direction' => 'asc', 'label' => 'Oldest First'],
                'price_high' => ['column' => 'price', 'direction' => 'desc', 'label' => 'Price High to Low'],
                'multi' => [
                    'columns' => [
                        ['column' => 'last_name', 'direction' => 'asc'],
                        ['column' => 'first_name', 'direction' => 'asc'],
                    ],
                    'label' => 'Name A-Z',
                ],
            ];
        }
    };
});

describe('Apply Sort Preset', function () {
    it('applies single column sort preset', function () {
        $this->sortable->applySortPreset('newest');

        expect($this->sortable->tableSortColumn)->toBe('created_at')
            ->and($this->sortable->tableSortDirection)->toBe('desc');
    });

    it('applies multi-column sort preset (uses first column)', function () {
        $this->sortable->applySortPreset('multi');

        expect($this->sortable->tableSortColumn)->toBe('last_name')
            ->and($this->sortable->tableSortDirection)->toBe('asc');
    });

    it('ignores invalid preset key', function () {
        $this->sortable->applySortPreset('nonexistent');

        expect($this->sortable->tableSortColumn)->toBeNull()
            ->and($this->sortable->tableSortDirection)->toBeNull();
    });
});

describe('Active Preset Tracking', function () {
    it('tracks active preset', function () {
        expect($this->sortable->getActiveSortPreset())->toBeNull();

        $this->sortable->applySortPreset('newest');
        expect($this->sortable->getActiveSortPreset())->toBe('newest');
    });

    it('checks if specific preset is active', function () {
        $this->sortable->applySortPreset('oldest');

        expect($this->sortable->isSortPresetActive('oldest'))->toBeTrue()
            ->and($this->sortable->isSortPresetActive('newest'))->toBeFalse();
    });
});

describe('Reset Sort', function () {
    it('resets sort to default', function () {
        $this->sortable->applySortPreset('newest');
        $this->sortable->resetSortPreset();

        expect($this->sortable->getActiveSortPreset())->toBeNull()
            ->and($this->sortable->tableSortColumn)->toBeNull()
            ->and($this->sortable->tableSortDirection)->toBeNull();
    });
});

describe('Sort Preset Options', function () {
    it('returns options array for UI', function () {
        $options = $this->sortable->getSortPresetOptions();

        expect($options)->toBe([
            'newest' => 'Newest First',
            'oldest' => 'Oldest First',
            'price_high' => 'Price High to Low',
            'multi' => 'Name A-Z',
        ]);
    });
});

describe('Export/Import Sort State', function () {
    it('exports current sort state', function () {
        $this->sortable->applySortPreset('newest');
        $state = $this->sortable->exportSortState();

        expect($state)->toBe([
            'preset' => 'newest',
            'column' => 'created_at',
            'direction' => 'desc',
        ]);
    });

    it('imports sort state with preset', function () {
        $this->sortable->importSortState([
            'preset' => 'oldest',
            'column' => 'created_at',
            'direction' => 'asc',
        ]);

        expect($this->sortable->getActiveSortPreset())->toBe('oldest')
            ->and($this->sortable->tableSortColumn)->toBe('created_at')
            ->and($this->sortable->tableSortDirection)->toBe('asc');
    });

    it('imports sort state without preset', function () {
        $this->sortable->importSortState([
            'preset' => null,
            'column' => 'name',
            'direction' => 'desc',
        ]);

        expect($this->sortable->getActiveSortPreset())->toBeNull()
            ->and($this->sortable->tableSortColumn)->toBe('name')
            ->and($this->sortable->tableSortDirection)->toBe('desc');
    });
});
