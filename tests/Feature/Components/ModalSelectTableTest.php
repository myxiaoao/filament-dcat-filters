<?php

use Cooper\FilamentDcatFilters\Components\ModalSelectTable;

it('can be instantiated', function () {
    $component = new ModalSelectTable;

    expect($component)->toBeInstanceOf(ModalSelectTable::class);
});

describe('Mount', function () {
    it('can mount with required parameters', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            titleColumn: 'name',
            keyColumn: 'id',
            multiple: false,
            displayColumns: [],
            searchColumns: [],
            selected: [],
            filterKey: 'test_filter'
        );

        expect($component->modelClass)->toBe('App\\Models\\User');
        expect($component->titleColumn)->toBe('name');
        expect($component->keyColumn)->toBe('id');
        expect($component->multiple)->toBeFalse();
        expect($component->filterKey)->toBe('test_filter');
    });

    it('can mount with custom display columns', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            displayColumns: ['id' => 'ID', 'name' => 'Name', 'email' => 'Email'],
            searchColumns: ['name', 'email'],
            filterKey: 'test'
        );

        expect($component->displayColumns)->toBe(['id' => 'ID', 'name' => 'Name', 'email' => 'Email']);
        expect($component->searchColumns)->toBe(['name', 'email']);
    });

    it('can mount with multiple selection', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            multiple: true,
            filterKey: 'test'
        );

        expect($component->multiple)->toBeTrue();
    });

    it('can mount with pre-selected items', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            selected: [1, 2, 3],
            filterKey: 'test'
        );

        expect($component->selected)->toBe([1, 2, 3]);
    });
});

describe('Row Selection', function () {
    it('can select a row in single selection mode', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            multiple: false,
            filterKey: 'test'
        );

        $component->selectRow(1);

        expect($component->selected)->toBe([1]);
    });

    it('replaces selection in single selection mode', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            multiple: false,
            selected: [1],
            filterKey: 'test'
        );

        $component->selectRow(2);

        expect($component->selected)->toBe([2]);
    });

    it('can add rows in multiple selection mode', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            multiple: true,
            filterKey: 'test'
        );

        $component->selectRow(1);
        $component->selectRow(2);

        expect($component->selected)->toBe([1, 2]);
    });

    it('can toggle row in multiple selection mode', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            multiple: true,
            selected: [1, 2],
            filterKey: 'test'
        );

        // Deselect row 1
        $component->selectRow(1);

        expect($component->selected)->toBe([2]);
    });
});

describe('Clear Selection', function () {
    it('can clear all selections', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            multiple: true,
            selected: [1, 2, 3],
            filterKey: 'test'
        );

        $component->clearSelection();

        expect($component->selected)->toBe([]);
    });

    it('increments render key on clear', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            filterKey: 'test'
        );

        $initialRenderKey = $component->renderKey;
        $component->clearSelection();

        expect($component->renderKey)->toBe($initialRenderKey + 1);
    });
});

describe('Column Building', function () {
    it('builds default columns when display columns not specified', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            titleColumn: 'name',
            keyColumn: 'id',
            displayColumns: [],
            filterKey: 'test'
        );

        $reflection = new ReflectionClass($component);
        $method = $reflection->getMethod('buildColumns');
        $method->setAccessible(true);

        $columns = $method->invoke($component);

        // Should have selection column + id column + title column
        expect($columns)->toHaveCount(3);
    });

    it('builds custom columns when display columns specified', function () {
        $component = new ModalSelectTable;
        $component->mount(
            modelClass: 'App\\Models\\User',
            displayColumns: ['id' => 'ID', 'name' => 'Name', 'email' => 'Email'],
            filterKey: 'test'
        );

        $reflection = new ReflectionClass($component);
        $method = $reflection->getMethod('buildColumns');
        $method->setAccessible(true);

        $columns = $method->invoke($component);

        // Should have selection column + 3 display columns
        expect($columns)->toHaveCount(4);
    });
});
