<?php

use Cooper\FilamentDcatFilters\Filters\FullTextFilter;
use Filament\Forms\Components\TextInput;

it('can be instantiated', function () {
    $filter = FullTextFilter::make('search');

    expect($filter)->toBeInstanceOf(FullTextFilter::class);
});

it('has correct default column span', function () {
    $filter = FullTextFilter::make('search');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Form Component', function () {
    it('uses text input component', function () {
        $filter = FullTextFilter::make('search');

        $form = $filter->getFormSchema();

        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(TextInput::class);
    });

    it('has no prefix icon', function () {
        $filter = FullTextFilter::make('search');

        $form = $filter->getFormSchema();

        expect($form[0]->getPrefixIcon())->toBeNull();
    });
});

describe('Search Columns', function () {
    it('can set search columns', function () {
        $filter = FullTextFilter::make('search')
            ->searchIn(['name', 'email', 'phone']);

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });

    it('can search relation columns', function () {
        $filter = FullTextFilter::make('search')
            ->searchIn(['name', 'department.name', 'manager.email']);

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });
});

describe('Configuration', function () {
    it('can set minimum length', function () {
        $filter = FullTextFilter::make('search')
            ->minLength(3);

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });

    it('can set debounce delay', function () {
        $filter = FullTextFilter::make('search')
            ->debounce(500);

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });

    it('can enable fulltext search', function () {
        $filter = FullTextFilter::make('search')
            ->searchIn(['title', 'content'])
            ->fullText();

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });

    it('can disable fulltext search', function () {
        $filter = FullTextFilter::make('search')
            ->searchIn(['title', 'content'])
            ->fullText(false);

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });

    it('can set custom placeholder', function () {
        $filter = FullTextFilter::make('search')
            ->placeholder('Search users...');

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });
});

describe('Quick Presets', function () {
    it('creates global search filter correctly', function () {
        $filter = FullTextFilter::global();

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
        expect($filter->getName())->toBe('search');
    });

    it('creates global search filter with custom name', function () {
        $filter = FullTextFilter::global('query');

        expect($filter->getName())->toBe('query');
    });
});

describe('Combined Features', function () {
    it('can combine columns with configuration', function () {
        $filter = FullTextFilter::make('search')
            ->searchIn(['name', 'email', 'phone'])
            ->minLength(2)
            ->debounce(300)
            ->placeholder('Search users...');

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });

    it('can combine fulltext with columns', function () {
        $filter = FullTextFilter::make('search')
            ->searchIn(['title', 'content', 'excerpt'])
            ->fullText()
            ->minLength(3);

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });
});

describe('Database Driver', function () {
    it('can set database driver', function () {
        $filter = FullTextFilter::make('search')
            ->driver('pgsql');

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });

    it('can combine driver with fulltext', function () {
        $filter = FullTextFilter::make('search')
            ->driver('pgsql')
            ->searchIn(['title', 'content'])
            ->fullText();

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });

    it('can combine driver with like search', function () {
        $filter = FullTextFilter::make('search')
            ->driver('pgsql')
            ->searchIn(['name', 'email']);

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });

    it('can combine driver with relation columns', function () {
        $filter = FullTextFilter::make('search')
            ->driver('pgsql')
            ->searchIn(['name', 'department.name']);

        expect($filter)->toBeInstanceOf(FullTextFilter::class);
    });
});
