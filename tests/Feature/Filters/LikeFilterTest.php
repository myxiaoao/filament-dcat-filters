<?php

use Cooper\FilamentDcatFilters\Filters\LikeFilter;

it('can be instantiated', function () {
    $filter = LikeFilter::make('title');

    expect($filter)->toBeInstanceOf(LikeFilter::class);
});

it('has correct default column span', function () {
    $filter = LikeFilter::make('title');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Case Sensitivity', function () {
    it('can set case insensitive search', function () {
        $filter = LikeFilter::make('title')->insensitive();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });

    it('can set case sensitive search', function () {
        $filter = LikeFilter::make('title')->sensitive();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });
});

describe('Wildcard Position', function () {
    it('can set wildcards at both ends', function () {
        $filter = LikeFilter::make('title')->wildcards('both');

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });

    it('can set starts with pattern', function () {
        $filter = LikeFilter::make('email')->startsWith();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });

    it('can set ends with pattern', function () {
        $filter = LikeFilter::make('email')->endsWith();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });

    it('can set exact pattern', function () {
        $filter = LikeFilter::make('code')->exact();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });
});

describe('Negation', function () {
    it('can negate filter', function () {
        $filter = LikeFilter::make('title')->negate();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });

    it('can use not like alias', function () {
        $filter = LikeFilter::make('title')->notLike();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });
});

describe('Operator', function () {
    it('can set custom operator', function () {
        $filter = LikeFilter::make('title')->operator('ilike');

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });
});

describe('Column Name', function () {
    it('uses filter name as column by default', function () {
        $filter = LikeFilter::make('title');

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });

    it('can set custom column name', function () {
        $filter = LikeFilter::make('search')
            ->column('title');

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });

    it('can combine column with other features', function () {
        $filter = LikeFilter::make('keyword_search')
            ->column('description')
            ->insensitive()
            ->startsWith();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });
});

describe('Pattern Building', function () {
    it('escapes special LIKE characters', function () {
        $filter = LikeFilter::make('title');

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('escapeLikeValue');
        $method->setAccessible(true);

        expect($method->invoke($filter, '50%'))->toBe('50\\%');
        expect($method->invoke($filter, 'under_score'))->toBe('under\\_score');
        expect($method->invoke($filter, 'back\\slash'))->toBe('back\\\\slash');
    });

    it('builds correct pattern for different wildcard positions', function () {
        $filter = LikeFilter::make('title');

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('buildPattern');
        $method->setAccessible(true);

        // Default (both)
        expect($method->invoke($filter, 'test'))->toBe('%test%');

        // Starts with
        $filter->startsWith();
        expect($method->invoke($filter, 'test'))->toBe('test%');

        // Ends with
        $filter->endsWith();
        expect($method->invoke($filter, 'test'))->toBe('%test');

        // Exact
        $filter->exact();
        expect($method->invoke($filter, 'test'))->toBe('test');
    });
});

describe('Relationship Support', function () {
    it('can set relationship', function () {
        $filter = LikeFilter::make('customer_name')
            ->relationship('customer', 'full_name');

        expect($filter)->toBeInstanceOf(LikeFilter::class);
        expect($filter->hasRelationship())->toBeTrue();
    });

    it('can combine relationship with insensitive', function () {
        $filter = LikeFilter::make('customer_name')
            ->relationship('customer', 'full_name')
            ->insensitive();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
        expect($filter->hasRelationship())->toBeTrue();
    });

    it('can combine relationship with column and wildcard', function () {
        $filter = LikeFilter::make('search')
            ->relationship('author', 'name')
            ->startsWith();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
        expect($filter->hasRelationship())->toBeTrue();
    });

    it('has no relationship by default', function () {
        $filter = LikeFilter::make('title');

        expect($filter->hasRelationship())->toBeFalse();
    });
});

describe('Database Driver', function () {
    it('can set database driver', function () {
        $filter = LikeFilter::make('title')
            ->driver('pgsql');

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });

    it('can combine driver with insensitive', function () {
        $filter = LikeFilter::make('title')
            ->driver('pgsql')
            ->insensitive();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });

    it('can combine driver with relationship', function () {
        $filter = LikeFilter::make('customer_name')
            ->driver('pgsql')
            ->relationship('customer', 'name')
            ->insensitive();

        expect($filter)->toBeInstanceOf(LikeFilter::class);
    });
});
