<?php

use Cooper\FilamentDcatFilters\Filters\SelectTableFilter;

it('can be instantiated', function () {
    $filter = SelectTableFilter::make('category_id');

    expect($filter)->toBeInstanceOf(SelectTableFilter::class);
});

it('has correct default column span', function () {
    $filter = SelectTableFilter::make('category_id');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Model Configuration', function () {
    it('can set model class', function () {
        $filter = SelectTableFilter::make('category_id')
            ->model('App\\Models\\Category');

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });

    it('can set relationship', function () {
        $filter = SelectTableFilter::make('category_id')
            ->relationship('category', 'name');

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });
});

describe('Searchable', function () {
    it('can enable searchable with true', function () {
        $filter = SelectTableFilter::make('category_id')
            ->searchable(true);

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });

    it('can disable searchable with false', function () {
        $filter = SelectTableFilter::make('category_id')
            ->searchable(false);

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });

    it('can set specific searchable columns', function () {
        $filter = SelectTableFilter::make('category_id')
            ->searchable(['name', 'description']);

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });
});

describe('Multiple Selection', function () {
    it('can enable multiple selection', function () {
        $filter = SelectTableFilter::make('category_id')
            ->multiple();

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });

    it('can disable multiple selection', function () {
        $filter = SelectTableFilter::make('category_id')
            ->multiple(false);

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });
});

describe('Query Modification', function () {
    it('can set query modifier', function () {
        $filter = SelectTableFilter::make('category_id')
            ->modifyQueryUsing(fn ($query) => $query->where('active', true));

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });
});

describe('Options Limit', function () {
    it('can set options limit', function () {
        $filter = SelectTableFilter::make('category_id')
            ->optionsLimit(50);

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });

    it('uses default options limit from config', function () {
        $filter = SelectTableFilter::make('category_id');

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('getOptionsLimit');
        $method->setAccessible(true);

        $limit = $method->invoke($filter);

        expect($limit)->toBeInt();
        expect($limit)->toBeGreaterThan(0);
    });
});

describe('Column Name', function () {
    it('uses filter name as column by default', function () {
        $filter = SelectTableFilter::make('category_id');

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });

    it('can set custom column name', function () {
        $filter = SelectTableFilter::make('category_selector')
            ->column('category_id')
            ->model('App\\Models\\Category');

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });

    it('can combine column with other features', function () {
        $filter = SelectTableFilter::make('tag_picker')
            ->column('tag_id')
            ->model('App\\Models\\Tag')
            ->multiple()
            ->searchable(['name']);

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });
});

describe('Chained Configuration', function () {
    it('can chain all configuration methods', function () {
        $filter = SelectTableFilter::make('category_id')
            ->model('App\\Models\\Category')
            ->searchable(['name', 'description'])
            ->multiple()
            ->optionsLimit(200)
            ->modifyQueryUsing(fn ($query) => $query);

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });
});
