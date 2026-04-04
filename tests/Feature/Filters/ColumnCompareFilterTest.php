<?php

use Cooper\FilamentDcatFilters\Filters\ColumnCompareFilter;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\Toggle;

it('can be instantiated', function () {
    $filter = ColumnCompareFilter::make('profitable');
    expect($filter)->toBeInstanceOf(ColumnCompareFilter::class);
});

describe('Configuration', function () {
    it('can set columns', function () {
        $filter = ColumnCompareFilter::make('profitable')
            ->leftColumn('price')
            ->rightColumn('cost');
        expect($filter->getLeftColumn())->toBe('price');
        expect($filter->getRightColumn())->toBe('cost');
    });

    it('can set operators', function () {
        $filter = ColumnCompareFilter::make('test')
            ->leftColumn('a')
            ->rightColumn('b')
            ->gte();
        expect($filter->getOperator())->toBe('>=');
    });

    it('supports all operators', function () {
        $filter = ColumnCompareFilter::make('test')->leftColumn('a')->rightColumn('b');
        expect($filter->gt()->getOperator())->toBe('>');
        expect($filter->gte()->getOperator())->toBe('>=');
        expect($filter->lt()->getOperator())->toBe('<');
        expect($filter->lte()->getOperator())->toBe('<=');
        expect($filter->eq()->getOperator())->toBe('=');
        expect($filter->ne()->getOperator())->toBe('!=');
    });
});

describe('Display Modes', function () {
    it('uses toggle by default', function () {
        $filter = ColumnCompareFilter::make('test')
            ->leftColumn('price')
            ->rightColumn('cost')
            ->gt();
        $form = $filter->getFormSchema();
        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(Toggle::class);
    });

    it('can use select mode', function () {
        $filter = ColumnCompareFilter::make('test')
            ->leftColumn('price')
            ->rightColumn('cost')
            ->select();
        expect($filter->isSelectMode())->toBeTrue();
    });
});

describe('Static Factory', function () {
    it('creates via comparing()', function () {
        $filter = ColumnCompareFilter::comparing('price', 'cost')->gt();
        expect($filter->getLeftColumn())->toBe('price');
        expect($filter->getRightColumn())->toBe('cost');
        expect($filter->getOperator())->toBe('>');
    });
});

describe('State Descriptor', function () {
    it('returns toggle type by default', function () {
        $filter = ColumnCompareFilter::make('test')
            ->leftColumn('a')
            ->rightColumn('b')
            ->gt();
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Toggle);
        expect($d->getFields())->toBe(['enabled']);
    });

    it('returns keyed type in select mode', function () {
        $filter = ColumnCompareFilter::make('test')
            ->leftColumn('a')
            ->rightColumn('b')
            ->select();
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Keyed);
        expect($d->getFields())->toBe(['operator']);
    });
});
