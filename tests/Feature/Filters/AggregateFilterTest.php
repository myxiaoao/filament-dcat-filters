<?php

use Cooper\FilamentDcatFilters\Filters\AggregateFilter;
use Cooper\FilamentDcatFilters\State\StateType;

it('can be instantiated', function () {
    $filter = AggregateFilter::make('order_count');
    expect($filter)->toBeInstanceOf(AggregateFilter::class);
});

describe('Configuration', function () {
    it('can set relationship and count', function () {
        $filter = AggregateFilter::make('order_count')
            ->relationship('orders')
            ->count()
            ->gte();
        expect($filter->getRelationshipName())->toBe('orders');
        expect($filter->getAggregateFunction())->toBe('count');
    });

    it('can set sum aggregate', function () {
        $filter = AggregateFilter::make('total')->relationship('orders')->sum('amount');
        expect($filter->getAggregateFunction())->toBe('sum');
        expect($filter->getAggregateColumn())->toBe('amount');
    });

    it('can set avg aggregate', function () {
        $filter = AggregateFilter::make('avg_rating')->relationship('reviews')->avg('rating');
        expect($filter->getAggregateFunction())->toBe('avg');
    });

    it('can set max aggregate', function () {
        $filter = AggregateFilter::make('max_price')->relationship('items')->max('price');
        expect($filter->getAggregateFunction())->toBe('max');
    });

    it('can set min aggregate', function () {
        $filter = AggregateFilter::make('min_score')->relationship('scores')->min('value');
        expect($filter->getAggregateFunction())->toBe('min');
    });

    it('can set constraint', function () {
        $filter = AggregateFilter::make('completed')
            ->relationship('orders')
            ->count()
            ->constrainedBy(fn ($q) => $q->where('status', 'completed'))
            ->gte();
        expect($filter)->toBeInstanceOf(AggregateFilter::class);
    });
});

describe('Static Factories', function () {
    it('creates via countOf()', function () {
        $filter = AggregateFilter::countOf('orders')->gte();
        expect($filter->getRelationshipName())->toBe('orders');
        expect($filter->getAggregateFunction())->toBe('count');
    });

    it('creates via sumOf()', function () {
        $filter = AggregateFilter::sumOf('orders', 'amount')->gte();
        expect($filter->getRelationshipName())->toBe('orders');
        expect($filter->getAggregateFunction())->toBe('sum');
        expect($filter->getAggregateColumn())->toBe('amount');
    });
});

describe('State Descriptor', function () {
    it('returns single type', function () {
        $filter = AggregateFilter::make('test')->relationship('orders')->count()->gte();
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Single);
        expect($d->getFields())->toBe(['value']);
    });

    it('isEmpty for non-numeric values', function () {
        $filter = AggregateFilter::make('test')->relationship('orders')->count()->gte();
        expect($filter->isStateEmpty(['value' => '']))->toBeTrue();
        expect($filter->isStateEmpty(['value' => 'abc']))->toBeTrue();
        expect($filter->isStateEmpty(['value' => '5']))->toBeFalse();
        expect($filter->isStateEmpty(['value' => '0']))->toBeFalse();
    });
});
