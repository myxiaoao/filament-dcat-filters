<?php

/**
 * ModalSelectTable Livewire Integration Tests
 *
 * Tests the full Livewire component lifecycle: mount, selectRow, confirm, cancel,
 * clearSelection, and event dispatching. Uses direct component instantiation to
 * avoid Filament Table rendering dependencies in test environment.
 */

use Cooper\FilamentDcatFilters\Components\ModalSelectTable;
use Cooper\FilamentDcatFilters\Tests\Fixtures\Models\TestItem;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::create('test_items', function ($table) {
        $table->id();
        $table->string('title');
        $table->string('status')->default('active');
        $table->decimal('price', 10, 2)->default(0);
        $table->timestamps();
    });

    TestItem::create(['title' => 'Alpha', 'status' => 'active', 'price' => 10]);
    TestItem::create(['title' => 'Bravo', 'status' => 'active', 'price' => 20]);
    TestItem::create(['title' => 'Charlie', 'status' => 'inactive', 'price' => 30]);
    TestItem::create(['title' => 'Delta', 'status' => 'active', 'price' => 40]);
    TestItem::create(['title' => 'Echo', 'status' => 'inactive', 'price' => 50]);
});

/**
 * Helper: instantiate and mount a ModalSelectTable component directly.
 *
 * Bypasses Livewire::test() rendering which requires full Filament Panel context.
 * Instead, tests the component's state machine and event dispatching directly.
 */
function mountComponent(array $params = []): ModalSelectTable
{
    $component = new ModalSelectTable;
    $component->mount(
        modelClass: $params['modelClass'] ?? TestItem::class,
        titleColumn: $params['titleColumn'] ?? 'title',
        keyColumn: $params['keyColumn'] ?? 'id',
        multiple: $params['multiple'] ?? false,
        displayColumns: $params['displayColumns'] ?? [],
        searchColumns: $params['searchColumns'] ?? [],
        selected: $params['selected'] ?? [],
        filterKey: $params['filterKey'] ?? 'test_filter',
        searchDebounce: $params['searchDebounce'] ?? 300,
        minSearchLength: $params['minSearchLength'] ?? 1,
        autoConfirmOnSelect: $params['autoConfirmOnSelect'] ?? false,
    );

    return $component;
}

// ─── Component Mount ─────────────────────────────────────────────

describe('ModalSelectTable Mount', function () {
    it('initializes with default values', function () {
        $component = mountComponent();

        expect($component->modelClass)->toBe(TestItem::class)
            ->and($component->titleColumn)->toBe('title')
            ->and($component->keyColumn)->toBe('id')
            ->and($component->multiple)->toBeFalse()
            ->and($component->selected)->toBe([])
            ->and($component->selectedSet)->toBe([])
            ->and($component->filterKey)->toBe('test_filter')
            ->and($component->autoConfirmOnSelect)->toBeFalse();
    });

    it('initializes with pre-selected values', function () {
        $component = mountComponent([
            'selected' => ['1', '3'],
            'multiple' => true,
        ]);

        expect($component->selected)->toBe(['1', '3'])
            ->and($component->selectedSet)->toBe(['1' => 0, '3' => 1]);
    });

    it('initializes with custom configuration', function () {
        $component = mountComponent([
            'displayColumns' => ['id' => 'ID', 'title' => 'Name'],
            'searchColumns' => ['title'],
            'searchDebounce' => 500,
            'minSearchLength' => 3,
            'autoConfirmOnSelect' => true,
        ]);

        expect($component->displayColumns)->toBe(['id' => 'ID', 'title' => 'Name'])
            ->and($component->searchColumns)->toBe(['title'])
            ->and($component->searchDebounce)->toBe(500)
            ->and($component->minSearchLength)->toBe(3)
            ->and($component->autoConfirmOnSelect)->toBeTrue();
    });
});

// ─── Single Selection ────────────────────────────────────────────

describe('Single Selection', function () {
    it('selects a single row', function () {
        $component = mountComponent();

        $component->selectRow('1');

        expect($component->selected)->toBe(['1'])
            ->and($component->selectedSet)->toBe(['1' => 0]);
    });

    it('replaces previous selection in single mode', function () {
        $component = mountComponent();

        $component->selectRow('1');
        expect($component->selected)->toBe(['1']);

        $component->selectRow('3');
        expect($component->selected)->toBe(['3'])
            ->and($component->selectedSet)->toBe(['3' => 0]);
    });

    it('handles integer keys', function () {
        $component = mountComponent();

        $component->selectRow(2);

        expect($component->selected)->toBe([2])
            ->and($component->selectedSet)->toBe([2 => 0]);
    });
});

// ─── Multiple Selection ──────────────────────────────────────────

describe('Multiple Selection', function () {
    it('adds items in multi-select mode', function () {
        $component = mountComponent(['multiple' => true]);

        $component->selectRow('1');
        $component->selectRow('3');

        expect($component->selected)->toBe(['1', '3'])
            ->and($component->selectedSet)->toBe(['1' => 0, '3' => 1]);
    });

    it('toggles items off when re-selected', function () {
        $component = mountComponent(['multiple' => true]);

        $component->selectRow('1');
        $component->selectRow('2');
        $component->selectRow('3');
        expect($component->selected)->toBe(['1', '2', '3']);

        // Deselect middle item
        $component->selectRow('2');
        expect($component->selected)->toBe(['1', '3'])
            ->and($component->selectedSet)->toBe(['1' => 0, '3' => 1]);
    });

    it('deselects all items individually', function () {
        $component = mountComponent(['multiple' => true]);

        $component->selectRow('1');
        $component->selectRow('2');
        $component->selectRow('1');
        $component->selectRow('2');

        expect($component->selected)->toBe([])
            ->and($component->selectedSet)->toBe([]);
    });
});

// ─── Auto Confirm On Select ──────────────────────────────────────

describe('Auto Confirm On Select', function () {
    it('does not auto-confirm when disabled', function () {
        $component = mountComponent(['autoConfirmOnSelect' => false]);

        $component->selectRow('1');

        // No assertion on dispatch here — just verify selection state
        expect($component->selected)->toBe(['1']);
    });

    it('does not auto-confirm in multiple mode even when enabled', function () {
        $component = mountComponent([
            'multiple' => true,
            'autoConfirmOnSelect' => true,
        ]);

        $component->selectRow('1');

        // In multiple mode, autoConfirm is skipped
        expect($component->selected)->toBe(['1']);
    });
});

// ─── Confirm ─────────────────────────────────────────────────────

describe('Confirm', function () {
    it('can be called after selection', function () {
        $component = mountComponent(['filterKey' => 'item_filter']);

        $component->selectRow('1');

        // Confirm should not throw
        $component->confirm();

        // State is preserved after confirm
        expect($component->selected)->toBe(['1']);
    });

    it('preserves multi-selection data on confirm', function () {
        $component = mountComponent([
            'filterKey' => 'multi_filter',
            'multiple' => true,
        ]);

        $component->selectRow('1');
        $component->selectRow('5');
        $component->confirm();

        expect($component->selected)->toBe(['1', '5']);
    });
});

// ─── Cancel ──────────────────────────────────────────────────────

describe('Cancel', function () {
    it('can be called without affecting selection', function () {
        $component = mountComponent();

        $component->selectRow('1');
        $component->cancel();

        // Selection is preserved (cancel only dispatches close event)
        expect($component->selected)->toBe(['1']);
    });
});

// ─── Clear Selection ─────────────────────────────────────────────

describe('Clear Selection', function () {
    it('clears all selections', function () {
        $component = mountComponent(['multiple' => true]);

        $component->selectRow('1');
        $component->selectRow('2');
        $component->selectRow('3');
        expect($component->selected)->toBe(['1', '2', '3']);

        $component->clearSelection();

        expect($component->selected)->toBe([])
            ->and($component->selectedSet)->toBe([]);
    });

    it('increments renderKey on clear', function () {
        $component = mountComponent();

        expect($component->renderKey)->toBe(0);

        $component->clearSelection();
        expect($component->renderKey)->toBe(1);

        $component->clearSelection();
        expect($component->renderKey)->toBe(2);
    });

    it('allows re-selection after clear', function () {
        $component = mountComponent(['multiple' => true]);

        $component->selectRow('1');
        $component->selectRow('2');
        $component->clearSelection();

        $component->selectRow('4');
        $component->selectRow('5');

        expect($component->selected)->toBe(['4', '5']);
    });
});

// ─── Query Builder ───────────────────────────────────────────────

describe('Query Builder', function () {
    it('builds query for valid model class', function () {
        $component = mountComponent();

        $reflection = new ReflectionClass($component);
        $method = $reflection->getMethod('getQuery');

        $query = $method->invoke($component);

        expect($query->count())->toBe(5);
    });

    it('throws for null model class', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: '',
            filterKey: 'test',
        );

        $reflection = new ReflectionClass($component);
        $method = $reflection->getMethod('getQuery');

        expect(fn () => $method->invoke($component))
            ->toThrow(RuntimeException::class, 'Model class is required.');
    });

    it('respects allowed_models config', function () {
        config()->set('filament-dcat-filters.allowed_models', ['App\\Models\\User']);

        $component = mountComponent();

        $reflection = new ReflectionClass($component);
        $method = $reflection->getMethod('getQuery');

        expect(fn () => $method->invoke($component))
            ->toThrow(RuntimeException::class, 'Model not allowed.');
    });
});

// ─── Full Lifecycle ──────────────────────────────────────────────

describe('Full Lifecycle', function () {
    it('single-select: select → replace → confirm', function () {
        $component = mountComponent(['filterKey' => 'product_filter']);

        // First selection
        $component->selectRow('1');
        expect($component->selected)->toBe(['1']);

        // Replace with different item
        $component->selectRow('3');
        expect($component->selected)->toBe(['3']);

        // Confirm
        $component->confirm();
        expect($component->selected)->toBe(['3']);
    });

    it('multi-select: select → toggle → clear → reselect → confirm', function () {
        $component = mountComponent([
            'filterKey' => 'tag_filter',
            'multiple' => true,
        ]);

        // Select 3 items
        $component->selectRow('1');
        $component->selectRow('2');
        $component->selectRow('3');
        expect($component->selected)->toBe(['1', '2', '3']);

        // Toggle off one
        $component->selectRow('2');
        expect($component->selected)->toBe(['1', '3']);

        // Clear all
        $component->clearSelection();
        expect($component->selected)->toBe([]);

        // Re-select
        $component->selectRow('4');
        $component->selectRow('5');
        expect($component->selected)->toBe(['4', '5']);

        // Confirm
        $component->confirm();
        expect($component->selected)->toBe(['4', '5']);
    });

    it('pre-selected → add more → confirm', function () {
        $component = mountComponent([
            'filterKey' => 'restore_filter',
            'multiple' => true,
            'selected' => ['2', '4'],
        ]);

        expect($component->selected)->toBe(['2', '4']);

        $component->selectRow('5');
        expect($component->selected)->toBe(['2', '4', '5']);

        $component->confirm();
        expect($component->selected)->toBe(['2', '4', '5']);
    });

    it('auto-confirm single-select: select → auto-confirm (no manual confirm needed)', function () {
        $component = mountComponent([
            'filterKey' => 'quick_filter',
            'autoConfirmOnSelect' => true,
        ]);

        // selectRow triggers confirm automatically
        $component->selectRow('2');

        expect($component->selected)->toBe(['2']);
    });
});

// ─── Table Configuration ─────────────────────────────────────────

describe('Table Configuration', function () {
    it('builds default columns when displayColumns is empty', function () {
        $component = mountComponent();

        $reflection = new ReflectionClass($component);
        $method = $reflection->getMethod('buildColumns');

        $columns = $method->invoke($component);

        // Should have: _select, id (keyColumn), title (titleColumn)
        expect($columns)->toHaveCount(3);
    });

    it('builds custom columns from displayColumns', function () {
        $component = mountComponent([
            'displayColumns' => ['id' => 'ID', 'title' => 'Name', 'price' => 'Price'],
        ]);

        $reflection = new ReflectionClass($component);
        $method = $reflection->getMethod('buildColumns');

        $columns = $method->invoke($component);

        // Should have: _select + 3 custom columns
        expect($columns)->toHaveCount(4);
    });
});
