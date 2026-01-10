<?php

use Cooper\FilamentDcatFilters\Filters\NullFilter;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;

it('can be instantiated', function () {
    $filter = NullFilter::make('deleted_at');

    expect($filter)->toBeInstanceOf(NullFilter::class);
});

it('has correct default column span', function () {
    $filter = NullFilter::make('deleted_at');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Display Styles', function () {
    it('uses select display style by default', function () {
        $filter = NullFilter::make('deleted_at');
        $form = $filter->getFormSchema();

        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(Select::class);
    });

    it('can change to radio display style', function () {
        $filter = NullFilter::make('deleted_at')
            ->radio();

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Radio::class);
    });

    it('can change back to select display style', function () {
        $filter = NullFilter::make('deleted_at')
            ->radio()
            ->select();

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Select::class);
    });
});

describe('Labels', function () {
    it('can set custom null label', function () {
        $filter = NullFilter::make('assigned_to')
            ->nullLabel('Not Assigned');

        expect($filter)->toBeInstanceOf(NullFilter::class);
    });

    it('can set custom not null label', function () {
        $filter = NullFilter::make('assigned_to')
            ->notNullLabel('Assigned');

        expect($filter)->toBeInstanceOf(NullFilter::class);
    });

    it('can set custom all label', function () {
        $filter = NullFilter::make('assigned_to')
            ->allLabel('Show All');

        expect($filter)->toBeInstanceOf(NullFilter::class);
    });

    it('can set all labels at once', function () {
        $filter = NullFilter::make('user_id')
            ->nullLabel('No User')
            ->notNullLabel('Has User')
            ->allLabel('Any');

        expect($filter)->toBeInstanceOf(NullFilter::class);
    });
});

describe('Radio Layout', function () {
    it('can set custom column count for radio layout', function () {
        $filter = NullFilter::make('deleted_at')
            ->radio()
            ->columns(2);

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Radio::class);
        expect($form[0]->getColumns())->toBe(['lg' => 2]);
    });
});

describe('Quick Presets', function () {
    it('creates deleted filter correctly', function () {
        $filter = NullFilter::deleted();

        expect($filter)->toBeInstanceOf(NullFilter::class);
        expect($filter->getName())->toBe('deleted_at');
    });

    it('creates deleted filter with custom name', function () {
        $filter = NullFilter::deleted('soft_deleted_at');

        expect($filter->getName())->toBe('soft_deleted_at');
    });

    it('creates assigned filter correctly', function () {
        $filter = NullFilter::assigned('user_id');

        expect($filter)->toBeInstanceOf(NullFilter::class);
        expect($filter->getName())->toBe('user_id');
    });

    it('creates empty filter correctly', function () {
        $filter = NullFilter::empty('description');

        expect($filter)->toBeInstanceOf(NullFilter::class);
        expect($filter->getName())->toBe('description');
    });
});

describe('Combined Features', function () {
    it('can combine display style with custom labels', function () {
        $filter = NullFilter::make('manager_id')
            ->label('Manager')
            ->nullLabel('No Manager')
            ->notNullLabel('Has Manager')
            ->radio()
            ->columns(2);

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Radio::class);
        expect($form[0]->getColumns())->toBe(['lg' => 2]);
    });
});
