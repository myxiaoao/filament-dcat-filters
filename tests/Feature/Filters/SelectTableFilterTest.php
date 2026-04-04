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

    it('can set relationship with model class', function () {
        $filter = SelectTableFilter::make('category_id')
            ->relationship('category', 'name', 'App\\Models\\Category');

        expect($filter->getModelClass())->toBe('App\\Models\\Category');
    });

    it('relationship with model class triggers form configuration', function () {
        $filter = SelectTableFilter::make('category_id')
            ->relationship('category', 'name', 'App\\Models\\Category');

        // Form should be configured (has form schema)
        $schema = $filter->getFormSchema();
        expect($schema)->not->toBeEmpty();
    });

    it('model then relationship preserves model class', function () {
        $filter = SelectTableFilter::make('category_id')
            ->model('App\\Models\\Category')
            ->relationship('category', 'name');

        expect($filter->getModelClass())->toBe('App\\Models\\Category');
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
            ->multiple();

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });
});

describe('Remote Search', function () {
    it('enables remote search mode', function () {
        $filter = SelectTableFilter::make('user_id')
            ->model('App\\Models\\User')
            ->remoteSearch();

        expect($filter->isRemoteSearchEnabled())->toBeTrue();
    });

    it('defaults to non-remote search', function () {
        $filter = SelectTableFilter::make('user_id')
            ->model('App\\Models\\User');

        expect($filter->isRemoteSearchEnabled())->toBeFalse();
    });

    it('can set search debounce', function () {
        $filter = SelectTableFilter::make('user_id')
            ->model('App\\Models\\User')
            ->remoteSearch()
            ->searchDebounce(500);

        expect($filter->getSearchDebounce())->toBe(500);
    });

    it('can set search columns', function () {
        $filter = SelectTableFilter::make('user_id')
            ->model('App\\Models\\User')
            ->remoteSearch()
            ->searchColumns(['name', 'email']);

        expect($filter->getSearchColumns())->toBe(['name', 'email']);
    });

    it('can set minimum search length', function () {
        $filter = SelectTableFilter::make('user_id')
            ->model('App\\Models\\User')
            ->remoteSearch()
            ->minSearchLength(3);

        expect($filter->getMinSearchLength())->toBe(3);
    });

    it('defaults debounce to 300ms', function () {
        $filter = SelectTableFilter::make('user_id');

        expect($filter->getSearchDebounce())->toBe(300);
    });
});

describe('Chained Configuration', function () {
    it('can chain all configuration methods', function () {
        $filter = SelectTableFilter::make('category_id')
            ->model('App\\Models\\Category')
            ->multiple()
            ->optionsLimit(200)
            ->modifyQueryUsing(fn ($query) => $query);

        expect($filter)->toBeInstanceOf(SelectTableFilter::class);
    });
});
