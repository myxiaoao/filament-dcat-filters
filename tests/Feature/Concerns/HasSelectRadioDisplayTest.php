<?php

use Cooper\FilamentDcatFilters\Filters\BooleanFilter;
use Cooper\FilamentDcatFilters\Filters\NullFilter;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;

describe('HasSelectRadioDisplay', function () {
    it('defaults to select display style', function () {
        $filter = BooleanFilter::make('test');
        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Select::class);
    });

    it('can switch to radio display style', function () {
        $filter = BooleanFilter::make('test')->radio();
        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Radio::class);
    });

    it('can switch back to select', function () {
        $filter = BooleanFilter::make('test')->radio()->select();
        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Select::class);
    });

    it('works with NullFilter too', function () {
        $filter = NullFilter::make('test')->radio();
        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Radio::class);
    });

    it('NullFilter defaults to select', function () {
        $filter = NullFilter::make('test');
        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(Select::class);
    });
});
