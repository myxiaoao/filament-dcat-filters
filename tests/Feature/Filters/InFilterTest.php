<?php

use Cooper\FilamentDcatFilters\Filters\InFilter;

it('can be instantiated', function () {
    $filter = InFilter::make('status');

    expect($filter)->toBeInstanceOf(InFilter::class);
});

it('has correct default column span', function () {
    $filter = InFilter::make('status');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Options', function () {
    it('can set options', function () {
        $filter = InFilter::make('status')
            ->options([
                'active' => 'Active',
                'inactive' => 'Inactive',
            ]);

        expect($filter)->toBeInstanceOf(InFilter::class);
    });
});

describe('Multiple Selection', function () {
    it('can enable multiple selection', function () {
        $filter = InFilter::make('tags')
            ->options(['php' => 'PHP', 'js' => 'JavaScript'])
            ->multiple();

        expect($filter)->toBeInstanceOf(InFilter::class);
    });

    it('can disable multiple selection', function () {
        $filter = InFilter::make('status')
            ->options(['active' => 'Active'])
            ->multiple(false);

        expect($filter)->toBeInstanceOf(InFilter::class);
    });
});

describe('Searchable', function () {
    it('can enable searchable', function () {
        $filter = InFilter::make('category')
            ->options(['cat1' => 'Category 1', 'cat2' => 'Category 2'])
            ->searchable();

        expect($filter)->toBeInstanceOf(InFilter::class);
    });

    it('can disable searchable', function () {
        $filter = InFilter::make('category')
            ->options(['cat1' => 'Category 1'])
            ->searchable(false);

        expect($filter)->toBeInstanceOf(InFilter::class);
    });
});

describe('Negation', function () {
    it('can negate filter', function () {
        $filter = InFilter::make('status')
            ->options(['active' => 'Active'])
            ->negate();

        expect($filter)->toBeInstanceOf(InFilter::class);
    });

    it('can use not in alias', function () {
        $filter = InFilter::make('status')
            ->options(['active' => 'Active'])
            ->notIn();

        expect($filter)->toBeInstanceOf(InFilter::class);
    });
});

describe('Column Name', function () {
    it('uses filter name as column by default', function () {
        $filter = InFilter::make('status');

        expect($filter)->toBeInstanceOf(InFilter::class);
    });

    it('can set custom column name', function () {
        $filter = InFilter::make('status_filter')
            ->column('status')
            ->options(['active' => 'Active', 'inactive' => 'Inactive']);

        expect($filter)->toBeInstanceOf(InFilter::class);
    });

    it('can combine column with other features', function () {
        $filter = InFilter::make('tag_filter')
            ->column('tag_id')
            ->options(['1' => 'Tag 1', '2' => 'Tag 2'])
            ->multiple()
            ->searchable();

        expect($filter)->toBeInstanceOf(InFilter::class);
    });
});

describe('Combined Features', function () {
    it('can combine multiple, searchable, and negate', function () {
        $filter = InFilter::make('excluded_tags')
            ->options([
                'spam' => 'Spam',
                'inappropriate' => 'Inappropriate',
                'outdated' => 'Outdated',
            ])
            ->multiple()
            ->searchable()
            ->negate();

        expect($filter)->toBeInstanceOf(InFilter::class);
    });
});
