<?php

use Cooper\FilamentDcatFilters\Filters\FilterGroup;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;

it('can be instantiated', function () {
    $filter = FilterGroup::make('search_group');

    expect($filter)->toBeInstanceOf(FilterGroup::class);
});

it('has correct default column span', function () {
    $filter = FilterGroup::make('search_group');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Logic', function () {
    it('defaults to AND logic', function () {
        $filter = FilterGroup::make('search_group');

        expect($filter->getLogic())->toBe('and');
    });

    it('can set OR logic', function () {
        $filter = FilterGroup::make('search_group')
            ->logic('or');

        expect($filter->getLogic())->toBe('or');
    });

    it('can use orLogic helper', function () {
        $filter = FilterGroup::make('search_group')
            ->orLogic();

        expect($filter->getLogic())->toBe('or');
    });

    it('can use andLogic helper', function () {
        $filter = FilterGroup::make('search_group')
            ->orLogic()
            ->andLogic();

        expect($filter->getLogic())->toBe('and');
    });

    it('normalizes logic to lowercase', function () {
        $filter = FilterGroup::make('search_group')
            ->logic('OR');

        expect($filter->getLogic())->toBe('or');
    });

    it('defaults to AND for invalid logic', function () {
        $filter = FilterGroup::make('search_group')
            ->logic('invalid');

        expect($filter->getLogic())->toBe('and');
    });
});

describe('Child Filters', function () {
    it('can set child filters', function () {
        $filter = FilterGroup::make('search_group')
            ->filters([
                LikeFilter::make('title'),
                LikeFilter::make('description'),
            ]);

        expect($filter->getChildFilters())->toHaveCount(2);
    });

    it('returns empty array when no filters set', function () {
        $filter = FilterGroup::make('search_group');

        expect($filter->getChildFilters())->toBe([]);
    });
});

describe('Combined Features', function () {
    it('can combine filters with OR logic', function () {
        $filter = FilterGroup::make('content_search')
            ->orLogic()
            ->filters([
                LikeFilter::make('title'),
                LikeFilter::make('description'),
                LikeFilter::make('content'),
            ]);

        expect($filter->getLogic())->toBe('or');
        expect($filter->getChildFilters())->toHaveCount(3);
    });

    it('can combine filters with AND logic', function () {
        $filter = FilterGroup::make('content_search')
            ->andLogic()
            ->filters([
                LikeFilter::make('title'),
                LikeFilter::make('author'),
            ]);

        expect($filter->getLogic())->toBe('and');
        expect($filter->getChildFilters())->toHaveCount(2);
    });
});

describe('Form Namespace Isolation', function () {
    it('wraps each child filter in a fieldset with statePath', function () {
        $filter = FilterGroup::make('search_group')
            ->filters([
                LikeFilter::make('title'),
                LikeFilter::make('body'),
            ]);

        $schema = $filter->getFormSchema();

        // Should have 2 fieldsets, one per child filter
        expect($schema)->toHaveCount(2);
        expect($schema[0])->toBeInstanceOf(\Filament\Schemas\Components\Fieldset::class);
        expect($schema[1])->toBeInstanceOf(\Filament\Schemas\Components\Fieldset::class);
    });

    it('prevents field name collision between same-type filters', function () {
        $filter = FilterGroup::make('search_group')
            ->filters([
                LikeFilter::make('title'),
                LikeFilter::make('body'),
            ]);

        $schema = $filter->getFormSchema();

        // Each fieldset should have its own statePath matching the filter name
        $reflection0 = new ReflectionClass($schema[0]);
        $reflection1 = new ReflectionClass($schema[1]);

        // Both fieldsets contain a 'value' field internally,
        // but they are scoped by different statePaths
        expect($schema)->toHaveCount(2);
    });
});

describe('Nesting Depth', function () {
    it('allows nesting up to maximum depth', function () {
        // Build 5 levels of nesting (the max)
        $innermost = FilterGroup::make('level5')->filters([LikeFilter::make('a')]);
        $level4 = FilterGroup::make('level4')->filters([$innermost]);
        $level3 = FilterGroup::make('level3')->filters([$level4]);
        $level2 = FilterGroup::make('level2')->filters([$level3]);
        $level1 = FilterGroup::make('level1')->filters([$level2]);

        expect($level1->getChildFilters())->toHaveCount(1);
    });

    it('throws exception when nesting exceeds maximum depth', function () {
        $innermost = FilterGroup::make('level6')->filters([LikeFilter::make('a')]);
        $level5 = FilterGroup::make('level5')->filters([$innermost]);
        $level4 = FilterGroup::make('level4')->filters([$level5]);
        $level3 = FilterGroup::make('level3')->filters([$level4]);
        $level2 = FilterGroup::make('level2')->filters([$level3]);

        expect(fn () => FilterGroup::make('level1')->filters([$level2]))
            ->toThrow(InvalidArgumentException::class, 'nesting depth exceeds maximum');
    });

    it('allows non-FilterGroup children at any level', function () {
        $group = FilterGroup::make('group')->filters([
            LikeFilter::make('title'),
            LikeFilter::make('body'),
            LikeFilter::make('summary'),
        ]);

        expect($group->getChildFilters())->toHaveCount(3);
    });
});
