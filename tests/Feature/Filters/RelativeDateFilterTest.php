<?php

use Cooper\FilamentDcatFilters\Filters\RelativeDateFilter;
use Filament\Forms\Components\Select;

it('can be instantiated', function () {
    $filter = RelativeDateFilter::make('created_at');

    expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
});

it('has correct default column span', function () {
    $filter = RelativeDateFilter::make('created_at');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Form Component', function () {
    it('uses select component', function () {
        $filter = RelativeDateFilter::make('created_at');

        $form = $filter->getFormSchema();

        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(Select::class);
    });
});

describe('Presets', function () {
    it('has default presets', function () {
        $filter = RelativeDateFilter::make('created_at');

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
    });

    it('can set custom presets', function () {
        $filter = RelativeDateFilter::make('created_at')
            ->presets([
                'custom_1' => 'Custom Period 1',
                'custom_2' => 'Custom Period 2',
            ]);

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
    });

    it('can add presets to existing ones', function () {
        $filter = RelativeDateFilter::make('created_at')
            ->addPresets([
                'last_90_days' => 'Last 90 Days',
            ]);

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
    });

    it('can use only specific presets', function () {
        $filter = RelativeDateFilter::make('created_at')
            ->only(['today', 'yesterday', 'last_7_days']);

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
    });

    it('can exclude specific presets', function () {
        $filter = RelativeDateFilter::make('created_at')
            ->except(['this_quarter', 'last_quarter']);

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
    });
});

describe('Quick Presets', function () {
    it('creates common filter correctly', function () {
        $filter = RelativeDateFilter::common('created_at');

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
        expect($filter->getName())->toBe('created_at');
    });

    it('creates weekly filter correctly', function () {
        $filter = RelativeDateFilter::weekly('created_at');

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
    });

    it('creates reporting filter correctly', function () {
        $filter = RelativeDateFilter::reporting('created_at');

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
    });
});

describe('Column Name', function () {
    it('uses filter name as column by default', function () {
        $filter = RelativeDateFilter::make('created_at');

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
    });

    it('can set custom column name', function () {
        $filter = RelativeDateFilter::make('date_filter')
            ->column('created_at');

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
    });

    it('can combine column with presets', function () {
        $filter = RelativeDateFilter::make('order_date')
            ->column('ordered_at')
            ->only(['today', 'yesterday', 'last_7_days']);

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
    });
});

describe('Combined Features', function () {
    it('can combine only and add presets', function () {
        $filter = RelativeDateFilter::make('created_at')
            ->only(['today', 'yesterday'])
            ->addPresets(['custom' => 'Custom Range']);

        expect($filter)->toBeInstanceOf(RelativeDateFilter::class);
    });
});
