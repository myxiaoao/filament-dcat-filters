<?php

use Carbon\Carbon;
use Cooper\FilamentDcatFilters\Filters\RangeFilter;
use Illuminate\Database\Eloquent\Builder;

it('can be instantiated', function () {
    $filter = RangeFilter::make('price');

    expect($filter)->toBeInstanceOf(RangeFilter::class);
});

it('has correct default column span', function () {
    $filter = RangeFilter::make('price');

    expect($filter->getColumnSpan())->toBe(2);
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

describe('Date Query', function () {
    it('normalizes date range query bounds to full days', function () {
        $filter = RangeFilter::make('created_at')->date();

        $mockQuery = Mockery::mock(Builder::class);
        $capturedData = null;

        $mockQuery->shouldReceive('whereBetween')
            ->once()
            ->andReturnUsing(function ($column, $range) use (&$capturedData, $mockQuery) {
                $capturedData = ['column' => $column, 'range' => $range];

                return $mockQuery;
            });

        $filter->apply($mockQuery, [
            'from' => '2024-01-01',
            'to' => '2024-01-15',
        ]);

        expect($capturedData['range'][0])->toBe('2024-01-01 00:00:00');
        expect($capturedData['range'][1])->toBe('2024-01-15 23:59:59');
    });

    it('normalizes date upper bound when only to is set', function () {
        $filter = RangeFilter::make('created_at')->date();

        $mockQuery = Mockery::mock(Builder::class);
        $capturedData = null;

        $mockQuery->shouldReceive('where')
            ->once()
            ->andReturnUsing(function ($column, $operator, $value) use (&$capturedData, $mockQuery) {
                $capturedData = compact('column', 'operator', 'value');

                return $mockQuery;
            });

        $filter->apply($mockQuery, [
            'from' => null,
            'to' => '2024-01-15 00:00:00',
        ]);

        expect($capturedData['operator'])->toBe('<=');
        expect($capturedData['value'])->toBe('2024-01-15 23:59:59');
    });

    it('normalizes date range indicators to full days', function () {
        $filter = RangeFilter::make('created_at')->date();

        $reflection = new ReflectionClass($filter);
        $indicateProp = $reflection->getProperty('indicateUsing');
        $indicateProp->setAccessible(true);
        $indicateFn = $indicateProp->getValue($filter);

        $bound = Closure::bind($indicateFn, $filter, get_class($filter));
        $result = $bound([
            'from' => '2024-01-01',
            'to' => '2024-01-15 00:00:00',
        ]);

        expect($result)->toHaveCount(2);
        expect($result[0]->getLabel())->toContain('2024-01-01 00:00:00');
        expect($result[1]->getLabel())->toContain('2024-01-15 23:59:59');
    });
});

describe('Datetime Query', function () {
    it('passes datetime values directly without modification', function () {
        $filter = RangeFilter::make('created_at')->datetime('Y-m-d H:i:s');

        $mockQuery = Mockery::mock(Builder::class);
        $capturedData = null;

        $mockQuery->shouldReceive('whereBetween')
            ->once()
            ->andReturnUsing(function ($column, $range) use (&$capturedData, $mockQuery) {
                $capturedData = ['column' => $column, 'range' => $range];

                return $mockQuery;
            });

        $filter->apply($mockQuery, [
            'from' => '2024-01-01 08:00:00',
            'to' => '2024-01-15 23:59:59',
        ]);

        expect($capturedData['range'][0])->toBe('2024-01-01 08:00:00');
        expect($capturedData['range'][1])->toBe('2024-01-15 23:59:59');
    });

    it('preserves 00:00:00 end time when explicitly set', function () {
        $filter = RangeFilter::make('created_at')->datetime('Y-m-d H:i:s');

        $mockQuery = Mockery::mock(Builder::class);
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

        expect($capturedData['range'][1])->toBe('2024-01-15 00:00:00');
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

    it('normalizes date bounds before converting to timestamp', function () {
        $filter = RangeFilter::make('created_at')
            ->date()
            ->toTimestamp();

        $mockQuery = Mockery::mock(Builder::class);
        $capturedData = null;

        $mockQuery->shouldReceive('whereBetween')
            ->once()
            ->andReturnUsing(function ($column, $range) use (&$capturedData, $mockQuery) {
                $capturedData = ['column' => $column, 'range' => $range];

                return $mockQuery;
            });

        $filter->apply($mockQuery, [
            'from' => '2024-01-01',
            'to' => '2024-01-15',
        ]);

        expect($capturedData['range'][0])->toBe(Carbon::parse('2024-01-01 00:00:00')->timestamp);
        expect($capturedData['range'][1])->toBe(Carbon::parse('2024-01-15 23:59:59')->timestamp);
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
