<?php

use Cooper\FilamentDcatFilters\Filters\BooleanFilter;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;

it('can be instantiated', function () {
    $filter = BooleanFilter::make('is_active');

    expect($filter)->toBeInstanceOf(BooleanFilter::class);
});

it('has correct default column span', function () {
    $filter = BooleanFilter::make('is_active');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Display Styles', function () {
    it('uses select display style by default', function () {
        $filter = BooleanFilter::make('is_active');
        $form = $filter->getFormSchema();

        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(Select::class);
    });

    it('can change to radio display style', function () {
        $filter = BooleanFilter::make('is_active')
            ->radio();

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Radio::class);
    });

    it('can change to toggle display style', function () {
        $filter = BooleanFilter::make('is_active')
            ->toggle();

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Toggle::class);
    });

    it('can change back to select display style', function () {
        $filter = BooleanFilter::make('is_active')
            ->toggle()
            ->select();

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Select::class);
    });
});

describe('Labels', function () {
    it('can set custom true label', function () {
        $filter = BooleanFilter::make('is_active')
            ->trueLabel('Active');

        expect($filter)->toBeInstanceOf(BooleanFilter::class);
    });

    it('can set custom false label', function () {
        $filter = BooleanFilter::make('is_active')
            ->falseLabel('Inactive');

        expect($filter)->toBeInstanceOf(BooleanFilter::class);
    });

    it('can set custom all label', function () {
        $filter = BooleanFilter::make('is_active')
            ->allLabel('Show All');

        expect($filter)->toBeInstanceOf(BooleanFilter::class);
    });

    it('can set all labels at once', function () {
        $filter = BooleanFilter::make('is_active')
            ->trueLabel('Yes')
            ->falseLabel('No')
            ->allLabel('All');

        expect($filter)->toBeInstanceOf(BooleanFilter::class);
    });
});

describe('Radio Layout', function () {
    it('can set custom column count for radio layout', function () {
        $filter = BooleanFilter::make('is_active')
            ->radio()
            ->columns(2);

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Radio::class);
        expect($form[0]->getColumns())->toBe(['lg' => 2]);
    });
});

describe('Quick Presets', function () {
    it('creates yes/no filter correctly', function () {
        $filter = BooleanFilter::yesNo('is_confirmed');

        expect($filter)->toBeInstanceOf(BooleanFilter::class);
        expect($filter->getName())->toBe('is_confirmed');
    });

    it('creates active filter correctly', function () {
        $filter = BooleanFilter::active();

        expect($filter)->toBeInstanceOf(BooleanFilter::class);
        expect($filter->getName())->toBe('is_active');
    });

    it('creates active filter with custom name', function () {
        $filter = BooleanFilter::active('is_enabled_user');

        expect($filter->getName())->toBe('is_enabled_user');
    });

    it('creates enabled filter correctly', function () {
        $filter = BooleanFilter::enabled();

        expect($filter)->toBeInstanceOf(BooleanFilter::class);
        expect($filter->getName())->toBe('is_enabled');
    });

    it('creates published filter correctly', function () {
        $filter = BooleanFilter::published();

        expect($filter)->toBeInstanceOf(BooleanFilter::class);
        expect($filter->getName())->toBe('is_published');
    });
});

describe('Combined Features', function () {
    it('can combine display style with custom labels', function () {
        $filter = BooleanFilter::make('is_premium')
            ->label('Account Type')
            ->trueLabel('Premium')
            ->falseLabel('Free')
            ->radio()
            ->columns(2);

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Radio::class);
        expect($form[0]->getColumns())->toBe(['lg' => 2]);
    });

    it('can use toggle with custom labels', function () {
        $filter = BooleanFilter::make('is_featured')
            ->trueLabel('Featured')
            ->falseLabel('Regular')
            ->toggle();

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Toggle::class);
    });
});
