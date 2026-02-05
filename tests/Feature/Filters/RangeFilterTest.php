<?php

use Cooper\FilamentDcatFilters\Filters\RangeFilter;

it('can be instantiated', function () {
    $filter = RangeFilter::make('price');

    expect($filter)->toBeInstanceOf(RangeFilter::class);
});

it('has correct default column span', function () {
    $filter = RangeFilter::make('price');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Numeric Range', function () {
    it('can create numeric range filter', function () {
        $filter = RangeFilter::make('price')->numeric();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can create integer range filter', function () {
        $filter = RangeFilter::make('quantity')->integer();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });
});

describe('Date Range', function () {
    it('can create date range filter', function () {
        $filter = RangeFilter::make('created_at')->date();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can create datetime range filter', function () {
        $filter = RangeFilter::make('created_at')->datetime();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can create time range filter', function () {
        $filter = RangeFilter::make('start_time')->time();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can set custom format', function () {
        $filter = RangeFilter::make('created_at')
            ->date()
            ->format('d/m/Y');

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });
});

describe('Datetime End Time Default', function () {
    it('adjusts end time from 00:00:00 to 23:59:59 for datetime filter with seconds', function () {
        $filter = RangeFilter::make('created_at')->datetime('Y-m-d H:i:s');

        $mockQuery = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $capturedData = null;

        $mockQuery->shouldReceive('whereBetween')
            ->once()
            ->andReturnUsing(function ($column, $range) use (&$capturedData, $mockQuery) {
                $capturedData = ['column' => $column, 'range' => $range];

                return $mockQuery;
            });

        $filter->apply($mockQuery, [
            'from' => '2024-01-01 08:00:00',
            'to' => '2024-01-15 00:00:00',
        ]);

        expect($capturedData['range'][1])->toBe('2024-01-15 23:59:59');
    });

    it('preserves end time when not 00:00:00', function () {
        $filter = RangeFilter::make('created_at')->datetime('Y-m-d H:i:s');

        $mockQuery = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $capturedData = null;

        $mockQuery->shouldReceive('whereBetween')
            ->once()
            ->andReturnUsing(function ($column, $range) use (&$capturedData, $mockQuery) {
                $capturedData = ['column' => $column, 'range' => $range];

                return $mockQuery;
            });

        $filter->apply($mockQuery, [
            'from' => '2024-01-01 08:00:00',
            'to' => '2024-01-15 18:30:00',
        ]);

        expect($capturedData['range'][1])->toBe('2024-01-15 18:30:00');
    });

    it('adjusts end time to 23:59:00 for datetime filter without seconds', function () {
        $filter = RangeFilter::make('created_at')->datetime('Y-m-d H:i');

        $mockQuery = Mockery::mock(\Illuminate\Database\Eloquent\Builder::class);
        $capturedData = null;

        $mockQuery->shouldReceive('whereBetween')
            ->once()
            ->andReturnUsing(function ($column, $range) use (&$capturedData, $mockQuery) {
                $capturedData = ['column' => $column, 'range' => $range];

                return $mockQuery;
            });

        $filter->apply($mockQuery, [
            'from' => '2024-01-01 08:00:00',
            'to' => '2024-01-15 00:00:00',
        ]);

        expect($capturedData['range'][1])->toBe('2024-01-15 23:59:00');
    });
});

describe('Placeholders', function () {
    it('can set placeholders with array', function () {
        $filter = RangeFilter::make('price')
            ->placeholders(['from' => 'Min', 'to' => 'Max'])
            ->numeric();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can set placeholders with two strings', function () {
        $filter = RangeFilter::make('price')
            ->placeholders('Min Price', 'Max Price')
            ->numeric();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });
});

describe('Timestamp Conversion', function () {
    it('can convert to timestamp', function () {
        $filter = RangeFilter::make('created_at')
            ->datetime()
            ->toTimestamp();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });
});

describe('Column Name', function () {
    it('uses filter name as column by default', function () {
        $filter = RangeFilter::make('price');

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can set custom column name', function () {
        $filter = RangeFilter::make('price_range')
            ->column('price')
            ->numeric();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });

    it('can combine column with date range', function () {
        $filter = RangeFilter::make('date_filter')
            ->column('created_at')
            ->datetime();

        expect($filter)->toBeInstanceOf(RangeFilter::class);
    });
});
