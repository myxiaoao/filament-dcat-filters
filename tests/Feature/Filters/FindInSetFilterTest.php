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

describe('Column Name', function () {
    it('uses filter name as column by default', function () {
        $filter = FindInSetFilter::make('tags');

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });

    it('can set custom column name', function () {
        $filter = FindInSetFilter::make('tag_filter')
            ->column('tags');

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });

    it('stores column name correctly', function () {
        $filter = FindInSetFilter::make('tag_filter')
            ->column('category_tags');

        $reflection = new ReflectionClass($filter);
        $prop = $reflection->getProperty('columnName');
        $prop->setAccessible(true);

        expect($prop->getValue($filter))->toBe('category_tags');
    });

    it('can combine column with other features', function () {
        $filter = FindInSetFilter::make('tag_search')
            ->column('tags')
            ->options(['php' => 'PHP', 'js' => 'JavaScript'])
            ->multiple()
            ->matchAny();

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });
});

describe('Database Driver', function () {
    it('can set database driver', function () {
        $filter = FindInSetFilter::make('tags')
            ->driver('pgsql');

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });

    it('can combine driver with column', function () {
        $filter = FindInSetFilter::make('tags')
            ->driver('pgsql')
            ->column('category_tags');

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });

    it('can combine driver with multiple and options', function () {
        $filter = FindInSetFilter::make('tags')
            ->driver('pgsql')
            ->options(['php' => 'PHP', 'js' => 'JavaScript'])
            ->multiple()
            ->matchAny();

        expect($filter)->toBeInstanceOf(FindInSetFilter::class);
    });
});
