<?php

use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Filters\BetweenFilter;
use Cooper\FilamentDcatFilters\Filters\BooleanFilter;
use Cooper\FilamentDcatFilters\Filters\CascadingSelectFilter;
use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;
use Cooper\FilamentDcatFilters\Filters\DateComponentFilter;
use Cooper\FilamentDcatFilters\Filters\EnumFilter;
use Cooper\FilamentDcatFilters\Filters\FilterGroup;
use Cooper\FilamentDcatFilters\Filters\FindInSetFilter;
use Cooper\FilamentDcatFilters\Filters\FullTextFilter;
use Cooper\FilamentDcatFilters\Filters\GeoLocationFilter;
use Cooper\FilamentDcatFilters\Filters\HiddenFilter;
use Cooper\FilamentDcatFilters\Filters\InFilter;
use Cooper\FilamentDcatFilters\Filters\InputMaskFilter;
use Cooper\FilamentDcatFilters\Filters\JsonFilter;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;
use Cooper\FilamentDcatFilters\Filters\ModalSelectFilter;
use Cooper\FilamentDcatFilters\Filters\NullFilter;
use Cooper\FilamentDcatFilters\Filters\RangeFilter;
use Cooper\FilamentDcatFilters\Filters\RegexFilter;
use Cooper\FilamentDcatFilters\Filters\RelativeDateFilter;
use Cooper\FilamentDcatFilters\Filters\ScopeFilter;
use Cooper\FilamentDcatFilters\Filters\SelectTableFilter;
use Cooper\FilamentDcatFilters\State\StateType;

describe('All filters have HasFilterState', function () {
    it('uses HasFilterState trait', function (string $filterClass) {
        $traits = class_uses_recursive($filterClass);
        expect($traits)->toHaveKey(HasFilterState::class);
    })->with([
        'BetweenFilter' => [BetweenFilter::class],
        'BooleanFilter' => [BooleanFilter::class],
        'CascadingSelectFilter' => [CascadingSelectFilter::class],
        'ComparisonFilter' => [ComparisonFilter::class],
        'DateComponentFilter' => [DateComponentFilter::class],
        'EnumFilter' => [EnumFilter::class],
        'FilterGroup' => [FilterGroup::class],
        'FindInSetFilter' => [FindInSetFilter::class],
        'FullTextFilter' => [FullTextFilter::class],
        'GeoLocationFilter' => [GeoLocationFilter::class],
        'HiddenFilter' => [HiddenFilter::class],
        'InFilter' => [InFilter::class],
        'InputMaskFilter' => [InputMaskFilter::class],
        'JsonFilter' => [JsonFilter::class],
        'LikeFilter' => [LikeFilter::class],
        'ModalSelectFilter' => [ModalSelectFilter::class],
        'NullFilter' => [NullFilter::class],
        'RangeFilter' => [RangeFilter::class],
        'RegexFilter' => [RegexFilter::class],
        'RelativeDateFilter' => [RelativeDateFilter::class],
        'ScopeFilter' => [ScopeFilter::class],
        'SelectTableFilter' => [SelectTableFilter::class],
    ]);
});

describe('Descriptor returns valid types', function () {
    it('returns correct state type', function (string $filterClass, StateType $expectedType) {
        $filter = $filterClass::make('test_field');
        $descriptor = $filter->getStateDescriptor();
        expect($descriptor->getType())->toBe($expectedType);
    })->with([
        'LikeFilter' => [LikeFilter::class, StateType::Single],
        'InFilter' => [InFilter::class, StateType::Single],
        'ComparisonFilter' => [ComparisonFilter::class, StateType::Single],
        'BooleanFilter' => [BooleanFilter::class, StateType::Single],
        'NullFilter' => [NullFilter::class, StateType::Single],
        'EnumFilter' => [EnumFilter::class, StateType::Single],
        'InputMaskFilter' => [InputMaskFilter::class, StateType::Single],
        'JsonFilter' => [JsonFilter::class, StateType::Single],
        'DateComponentFilter' => [DateComponentFilter::class, StateType::Single],
        'HiddenFilter' => [HiddenFilter::class, StateType::Single],
        'FindInSetFilter' => [FindInSetFilter::class, StateType::Single],
        'SelectTableFilter' => [SelectTableFilter::class, StateType::Single],
        'ModalSelectFilter' => [ModalSelectFilter::class, StateType::Single],
        'RangeFilter' => [RangeFilter::class, StateType::Range],
        'BetweenFilter' => [BetweenFilter::class, StateType::Range],
        'RegexFilter' => [RegexFilter::class, StateType::Keyed],
        'FullTextFilter' => [FullTextFilter::class, StateType::Keyed],
        'ScopeFilter' => [ScopeFilter::class, StateType::Keyed],
        'RelativeDateFilter' => [RelativeDateFilter::class, StateType::Keyed],
        'GeoLocationFilter' => [GeoLocationFilter::class, StateType::Composite],
        'CascadingSelectFilter' => [CascadingSelectFilter::class, StateType::Composite],
        'FilterGroup' => [FilterGroup::class, StateType::Composite],
    ]);
});

describe('Descriptor declares non-empty fields', function () {
    it('declares at least one field for static-field filters', function (string $filterClass) {
        $filter = $filterClass::make('test_field');
        $descriptor = $filter->getStateDescriptor();

        expect($descriptor->getFields())->not->toBeEmpty();
    })->with([
        'LikeFilter' => [LikeFilter::class],
        'InFilter' => [InFilter::class],
        'ComparisonFilter' => [ComparisonFilter::class],
        'BooleanFilter' => [BooleanFilter::class],
        'NullFilter' => [NullFilter::class],
        'InputMaskFilter' => [InputMaskFilter::class],
        'JsonFilter' => [JsonFilter::class],
        'DateComponentFilter' => [DateComponentFilter::class],
        'HiddenFilter' => [HiddenFilter::class],
        'RangeFilter' => [RangeFilter::class],
        'BetweenFilter' => [BetweenFilter::class],
        'RegexFilter' => [RegexFilter::class],
        'FullTextFilter' => [FullTextFilter::class],
        'ScopeFilter' => [ScopeFilter::class],
        'RelativeDateFilter' => [RelativeDateFilter::class],
        'GeoLocationFilter' => [GeoLocationFilter::class],
        'ModalSelectFilter' => [ModalSelectFilter::class],
        'SelectTableFilter' => [SelectTableFilter::class],
    ]);

    it('declares empty fields for unconfigured dynamic filters', function (string $filterClass) {
        $filter = $filterClass::make('test_field');
        $descriptor = $filter->getStateDescriptor();

        // Before addLevel()/filters() is called, fields are empty — this is expected
        expect($descriptor->getFields())->toBeEmpty();
    })->with([
        'CascadingSelectFilter' => [CascadingSelectFilter::class],
        'FilterGroup' => [FilterGroup::class],
    ]);
});

describe('Dynamic type filters', function () {
    it('InFilter changes to Multiple when multiple() is called', function () {
        $filter = InFilter::make('test')->options(['a' => 'A'])->multiple();
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Multiple)
            ->and($d->getFields())->toBe(['values'])
            ->and($d->getCapabilities())->toContain('multiple');
    });

    it('EnumFilter changes to Multiple when multiple() is called', function () {
        $filter = EnumFilter::make('test')->multiple();
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Multiple)
            ->and($d->getFields())->toBe(['values']);
    });

    it('SelectTableFilter changes to Multiple when multiple() is called', function () {
        $filter = SelectTableFilter::make('test')->multiple();
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Multiple)
            ->and($d->getFields())->toBe(['values']);
    });

    it('RegexFilter returns Toggle when pattern mode is enabled', function () {
        $filter = RegexFilter::make('test')->pattern('^test$');
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Toggle)
            ->and($d->getFields())->toBe(['enabled']);
    });

    it('RegexFilter returns Keyed when in user pattern mode', function () {
        $filter = RegexFilter::make('test');
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Keyed)
            ->and($d->getFields())->toBe(['pattern']);
    });
});

describe('isEmpty correctness', function () {
    it('BooleanFilter treats 0 as non-empty', function () {
        $filter = BooleanFilter::make('test');
        expect($filter->isStateEmpty(['value' => '0']))->toBeFalse();
        expect($filter->isStateEmpty(['value' => '']))->toBeTrue();
    });

    it('NullFilter treats null string as non-empty', function () {
        $filter = NullFilter::make('test');
        expect($filter->isStateEmpty(['value' => 'null']))->toBeFalse();
        expect($filter->isStateEmpty(['value' => '']))->toBeTrue();
    });

    it('GeoLocationFilter is empty when latitude is missing', function () {
        $filter = GeoLocationFilter::make('test');
        expect($filter->isStateEmpty(['latitude' => '', 'longitude' => '100', 'radius' => '10']))->toBeTrue();
    });

    it('GeoLocationFilter is non-empty when lat and lon present', function () {
        $filter = GeoLocationFilter::make('test');
        expect($filter->isStateEmpty(['latitude' => '40', 'longitude' => '-74', 'radius' => '10']))->toBeFalse();
    });

    it('RangeFilter is empty when both from and to are empty', function () {
        $filter = RangeFilter::make('test');
        expect($filter->isStateEmpty(['from' => '', 'to' => '']))->toBeTrue();
    });

    it('RangeFilter is non-empty when from has value', function () {
        $filter = RangeFilter::make('test');
        expect($filter->isStateEmpty(['from' => '10', 'to' => '']))->toBeFalse();
    });

    it('ComparisonFilter treats non-numeric as empty', function () {
        $filter = ComparisonFilter::make('test')->gte();
        expect($filter->isStateEmpty(['value' => 'abc']))->toBeTrue();
        expect($filter->isStateEmpty(['value' => '42']))->toBeFalse();
        expect($filter->isStateEmpty(['value' => '0']))->toBeFalse();
    });
});
