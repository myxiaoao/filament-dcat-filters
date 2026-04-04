<?php

use Cooper\FilamentDcatFilters\Filters\SoftDeleteFilter;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\Toggle;

it('can be instantiated', function () {
    $filter = SoftDeleteFilter::make('trashed');
    expect($filter)->toBeInstanceOf(SoftDeleteFilter::class);
});

it('has correct default column span', function () {
    $filter = SoftDeleteFilter::make('trashed');
    expect($filter->getColumnSpan())->toBe(1);
});

describe('Display Modes', function () {
    it('uses select by default', function () {
        $filter = SoftDeleteFilter::make('trashed');
        $form = $filter->getFormSchema();
        expect($form)->toHaveCount(1);
    });

    it('can use toggle mode', function () {
        $filter = SoftDeleteFilter::make('trashed')->toggle();
        $form = $filter->getFormSchema();
        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(Toggle::class);
    });
});

describe('Labels', function () {
    it('can set custom labels', function () {
        $filter = SoftDeleteFilter::make('trashed')
            ->withoutTrashedLabel('Active')
            ->onlyTrashedLabel('Deleted')
            ->withTrashedLabel('All Records');

        expect($filter)->toBeInstanceOf(SoftDeleteFilter::class);
    });
});

describe('State Descriptor', function () {
    it('returns keyed type by default', function () {
        $filter = SoftDeleteFilter::make('trashed');
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Keyed);
        expect($d->getFields())->toBe(['trashed']);
    });

    it('returns toggle type in toggle mode', function () {
        $filter = SoftDeleteFilter::make('trashed')->toggle();
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Toggle);
    });
});
