<?php

use Cooper\FilamentDcatFilters\Filters\FindInSetFilter;
use Filament\Forms\Components\Select;

it('can be instantiated', function () {
    $filter = FindInSetFilter::make('tags');

    expect($filter)->toBeInstanceOf(FindInSetFilter::class);
});

it('has correct default column span', function () {
    $filter = FindInSetFilter::make('tags');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Form Component', function () {
    it('uses select component', function () {
        $filter = FindInSetFilter::make('tags');

        $form = $filter->getFormSchema();

        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(Select::class);
    });
});

describe('Options', function () {
    it('can set options as array', function () {
        $filter = FindInSetFilter::make('tags')
            ->options([
                'php' => 'PHP',
                'laravel' => 'Laravel',
                'filament' => 'Filament',
            ]);

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });

    it('can set options as closure', function () {
        $filter = FindInSetFilter::make('tags')
            ->options(fn () => [
                'php' => 'PHP',
                'laravel' => 'Laravel',
            ]);

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });
});

describe('Multiple Selection', function () {
    it('can enable multiple selection', function () {
        $filter = FindInSetFilter::make('tags')
            ->options(['php' => 'PHP', 'laravel' => 'Laravel'])
            ->multiple();

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });

    it('can disable multiple selection', function () {
        $filter = FindInSetFilter::make('tags')
            ->options(['php' => 'PHP', 'laravel' => 'Laravel'])
            ->multiple(false);

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });
});

describe('Searchable', function () {
    it('can make select searchable', function () {
        $filter = FindInSetFilter::make('tags')
            ->options(['php' => 'PHP', 'laravel' => 'Laravel'])
            ->searchable();

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });

    it('can disable searchable', function () {
        $filter = FindInSetFilter::make('tags')
            ->options(['php' => 'PHP', 'laravel' => 'Laravel'])
            ->searchable(false);

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });
});

describe('Placeholder', function () {
    it('can set custom placeholder', function () {
        $filter = FindInSetFilter::make('tags')
            ->options(['php' => 'PHP'])
            ->placeholder('Select tags...');

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });
});

describe('Match Logic', function () {
    it('can use match any (OR) logic', function () {
        $filter = FindInSetFilter::make('tags')
            ->options(['php' => 'PHP', 'laravel' => 'Laravel'])
            ->multiple()
            ->matchAny();

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });

    it('can use match all (AND) logic', function () {
        $filter = FindInSetFilter::make('tags')
            ->options(['php' => 'PHP', 'laravel' => 'Laravel'])
            ->multiple()
            ->matchAll();

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });
});

describe('Combined Features', function () {
    it('can combine multiple features', function () {
        $filter = FindInSetFilter::make('categories')
            ->options([
                'tech' => 'Technology',
                'science' => 'Science',
                'art' => 'Art',
            ])
            ->multiple()
            ->searchable()
            ->placeholder('Filter by categories...')
            ->matchAny();

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });
});
