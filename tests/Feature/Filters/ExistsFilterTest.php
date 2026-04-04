<?php

use Cooper\FilamentDcatFilters\Filters\ExistsFilter;
use Filament\Forms\Components\Toggle;

it('can be instantiated', function () {
    $filter = ExistsFilter::make('has_comments');
    expect($filter)->toBeInstanceOf(ExistsFilter::class);
});

describe('Configuration', function () {
    it('can set relationship', function () {
        $filter = ExistsFilter::make('has_comments')->relationship('comments');
        expect($filter->getRelationshipName())->toBe('comments');
    });

    it('can set notExists', function () {
        $filter = ExistsFilter::make('no_orders')->relationship('orders')->notExists();
        expect($filter)->toBeInstanceOf(ExistsFilter::class);
    });

    it('can set constraint', function () {
        $filter = ExistsFilter::make('has_comments')
            ->relationship('comments')
            ->constrainedBy(fn ($q) => $q->where('status', 'published'));
        expect($filter)->toBeInstanceOf(ExistsFilter::class);
    });
});

describe('Display Modes', function () {
    it('uses toggle by default', function () {
        $filter = ExistsFilter::make('has_comments')->relationship('comments');
        $form = $filter->getFormSchema();
        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(Toggle::class);
    });

    it('can use select mode', function () {
        $filter = ExistsFilter::make('has_comments')->relationship('comments')->select();
        $form = $filter->getFormSchema();
        expect($form)->toHaveCount(1);
    });
});

describe('Static Factories', function () {
    it('can create via forExists()', function () {
        $filter = ExistsFilter::forExists('comments');
        expect($filter)->toBeInstanceOf(ExistsFilter::class);
        expect($filter->getRelationshipName())->toBe('comments');
    });

    it('can create via forNotExists()', function () {
        $filter = ExistsFilter::forNotExists('orders');
        expect($filter)->toBeInstanceOf(ExistsFilter::class);
        expect($filter->getRelationshipName())->toBe('orders');
    });
});

describe('State Descriptor', function () {
    it('returns toggle type by default', function () {
        $filter = ExistsFilter::make('has_comments')->relationship('comments');
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(\Cooper\FilamentDcatFilters\State\StateType::Toggle);
    });

    it('returns single type in select mode', function () {
        $filter = ExistsFilter::make('has_comments')->relationship('comments')->select();
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(\Cooper\FilamentDcatFilters\State\StateType::Single);
    });
});
