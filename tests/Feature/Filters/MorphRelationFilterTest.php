<?php

use Cooper\FilamentDcatFilters\Filters\MorphRelationFilter;
use Cooper\FilamentDcatFilters\State\StateType;

it('can be instantiated', function () {
    $filter = MorphRelationFilter::make('commentable');
    expect($filter)->toBeInstanceOf(MorphRelationFilter::class);
});

describe('MorphTo Mode', function () {
    it('can set morphTo mode', function () {
        $filter = MorphRelationFilter::make('commentable')->morphTo();
        expect($filter->getMorphMode())->toBe('morphTo');
    });

    it('can set types with model classes', function () {
        $filter = MorphRelationFilter::make('commentable')
            ->morphTo()
            ->types([
                'post' => 'App\\Models\\Post',
                'video' => 'App\\Models\\Video',
            ]);
        expect($filter)->toBeInstanceOf(MorphRelationFilter::class);
    });

    it('can set types with labels', function () {
        $filter = MorphRelationFilter::make('commentable')
            ->morphTo()
            ->types([
                'post' => ['model' => 'App\\Models\\Post', 'label' => 'Articles'],
            ]);
        expect($filter)->toBeInstanceOf(MorphRelationFilter::class);
    });
});

describe('MorphToMany Mode', function () {
    it('can set morphToMany mode', function () {
        $filter = MorphRelationFilter::make('tags')->morphToMany();
        expect($filter->getMorphMode())->toBe('morphToMany');
    });

    it('can set model class', function () {
        $filter = MorphRelationFilter::make('tags')
            ->morphToMany()
            ->model('App\\Models\\Tag');
        expect($filter->getModelClass())->toBe('App\\Models\\Tag');
    });

    it('can enable multiple selection', function () {
        $filter = MorphRelationFilter::make('tags')
            ->morphToMany()
            ->model('App\\Models\\Tag')
            ->multiple();
        expect($filter)->toBeInstanceOf(MorphRelationFilter::class);
    });

    it('can set constraint', function () {
        $filter = MorphRelationFilter::make('tags')
            ->morphToMany()
            ->model('App\\Models\\Tag')
            ->constrainedBy(fn ($q) => $q->where('is_active', true));
        expect($filter)->toBeInstanceOf(MorphRelationFilter::class);
    });
});

describe('State Descriptor', function () {
    it('returns Single for morphTo', function () {
        $filter = MorphRelationFilter::make('commentable')
            ->morphTo()
            ->types(['post' => 'App\\Models\\Post']);
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Single);
        expect($d->getFields())->toBe(['value']);
    });

    it('returns Single for morphToMany single select', function () {
        $filter = MorphRelationFilter::make('tags')
            ->morphToMany()
            ->model('App\\Models\\Tag');
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Single);
    });

    it('returns Multiple for morphToMany multiple select', function () {
        $filter = MorphRelationFilter::make('tags')
            ->morphToMany()
            ->model('App\\Models\\Tag')
            ->multiple();
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Multiple);
        expect($d->getFields())->toBe(['values']);
    });
});
