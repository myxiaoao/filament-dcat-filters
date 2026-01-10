<?php

use Cooper\FilamentDcatFilters\Filters\ScopeFilter;

it('can be instantiated', function () {
    $filter = ScopeFilter::make('status');

    expect($filter)->toBeInstanceOf(ScopeFilter::class);
});

it('has correct default column span', function () {
    $filter = ScopeFilter::make('status');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Scopes Configuration', function () {
    it('can set scopes with array', function () {
        $filter = ScopeFilter::make('status')
            ->scopes([
                'all' => ['label' => 'All', 'default' => true],
                'active' => [
                    'label' => 'Active',
                    'query' => fn ($q) => $q->where('status', 'active'),
                ],
            ]);

        expect($filter)->toBeInstanceOf(ScopeFilter::class);
    });

    it('can set simple string labels', function () {
        $filter = ScopeFilter::make('type')
            ->scopes([
                'all' => 'All Types',
                'featured' => 'Featured',
            ]);

        expect($filter)->toBeInstanceOf(ScopeFilter::class);
    });

    it('identifies default scope', function () {
        $filter = ScopeFilter::make('status')
            ->scopes([
                'all' => ['label' => 'All'],
                'active' => ['label' => 'Active', 'default' => true],
            ]);

        $reflection = new ReflectionClass($filter);
        $property = $reflection->getProperty('defaultScope');
        $property->setAccessible(true);

        expect($property->getValue($filter))->toBe('active');
    });

    it('uses first scope as default when none specified', function () {
        $filter = ScopeFilter::make('status')
            ->scopes([
                'pending' => ['label' => 'Pending'],
                'active' => ['label' => 'Active'],
            ]);

        $reflection = new ReflectionClass($filter);
        $property = $reflection->getProperty('defaultScope');
        $property->setAccessible(true);

        expect($property->getValue($filter))->toBe('pending');
    });
});

describe('Display Style', function () {
    it('can set select style', function () {
        $filter = ScopeFilter::make('status')
            ->scopes(['all' => 'All'])
            ->style('select');

        expect($filter)->toBeInstanceOf(ScopeFilter::class);
    });

    it('can set radio style', function () {
        $filter = ScopeFilter::make('status')
            ->scopes(['all' => 'All'])
            ->style('radio');

        expect($filter)->toBeInstanceOf(ScopeFilter::class);
    });
});

describe('Columns', function () {
    it('can set number of columns', function () {
        $filter = ScopeFilter::make('status')
            ->scopes(['all' => 'All', 'active' => 'Active'])
            ->columns(3);

        expect($filter)->toBeInstanceOf(ScopeFilter::class);
    });

    it('can set columns as array', function () {
        $filter = ScopeFilter::make('status')
            ->scopes(['all' => 'All'])
            ->columns(['sm' => 2, 'md' => 4]);

        expect($filter)->toBeInstanceOf(ScopeFilter::class);
    });
});

describe('Badge', function () {
    it('can enable badge display', function () {
        $filter = ScopeFilter::make('status')
            ->scopes(['all' => 'All'])
            ->badge(true);

        expect($filter)->toBeInstanceOf(ScopeFilter::class);
    });

    it('can disable badge display', function () {
        $filter = ScopeFilter::make('status')
            ->scopes(['all' => 'All'])
            ->badge(false);

        expect($filter)->toBeInstanceOf(ScopeFilter::class);
    });
});

describe('Preset Filters', function () {
    it('can create status filter', function () {
        $filter = ScopeFilter::forStatus('status', [
            'draft' => 'Draft',
            'published' => 'Published',
        ]);

        expect($filter)->toBeInstanceOf(ScopeFilter::class);
    });

    it('can create date filter', function () {
        $filter = ScopeFilter::forDates('created_at');

        expect($filter)->toBeInstanceOf(ScopeFilter::class);
    });
});
