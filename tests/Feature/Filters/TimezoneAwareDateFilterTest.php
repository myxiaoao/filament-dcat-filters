<?php

use Cooper\FilamentDcatFilters\Filters\TimezoneAwareDateFilter;
use Cooper\FilamentDcatFilters\State\StateType;

it('can be instantiated', function () {
    $filter = TimezoneAwareDateFilter::make('created_at');
    expect($filter)->toBeInstanceOf(TimezoneAwareDateFilter::class);
});

describe('Configuration', function () {
    it('can set user timezone as string', function () {
        $filter = TimezoneAwareDateFilter::make('created_at')
            ->userTimezone('Asia/Shanghai');
        expect($filter->getUserTimezone())->toBe('Asia/Shanghai');
    });

    it('can set user timezone as closure', function () {
        $filter = TimezoneAwareDateFilter::make('created_at')
            ->userTimezone(fn () => 'Asia/Tokyo');
        expect($filter->getUserTimezone())->toBe('Asia/Tokyo');
    });

    it('can set database timezone', function () {
        $filter = TimezoneAwareDateFilter::make('created_at')
            ->userTimezone('Asia/Shanghai')
            ->databaseTimezone('America/New_York');
        expect($filter->getDatabaseTimezone())->toBe('America/New_York');
    });

    it('defaults database timezone to UTC', function () {
        $filter = TimezoneAwareDateFilter::make('created_at');
        expect($filter->getDatabaseTimezone())->toBe('UTC');
    });

    it('can switch to datetime mode', function () {
        $filter = TimezoneAwareDateFilter::make('created_at')
            ->userTimezone('Asia/Shanghai')
            ->datetime();
        expect($filter->isDatetime())->toBeTrue();
    });

    it('defaults to date mode', function () {
        $filter = TimezoneAwareDateFilter::make('created_at')
            ->userTimezone('Asia/Shanghai');
        expect($filter->isDatetime())->toBeFalse();
    });
});

describe('State Descriptor', function () {
    it('returns Range type', function () {
        $filter = TimezoneAwareDateFilter::make('created_at')
            ->userTimezone('Asia/Shanghai');
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Range);
        expect($d->getFields())->toBe(['from', 'to']);
    });

    it('supports all database drivers', function () {
        $filter = TimezoneAwareDateFilter::make('created_at')
            ->userTimezone('UTC');
        $d = $filter->getStateDescriptor();
        expect($d->getDatabaseSupport())->toBe(['mysql', 'pgsql', 'sqlite']);
    });
});

describe('Timezone Conversion', function () {
    it('converts date from user timezone to DB timezone', function () {
        $filter = TimezoneAwareDateFilter::make('created_at')
            ->userTimezone('Asia/Shanghai')
            ->databaseTimezone('UTC');

        $ref = new ReflectionClass($filter);
        $method = $ref->getMethod('convertToDbTimezone');
        $method->setAccessible(true);

        // 2024-06-15 in Shanghai = 2024-06-14 16:00:00 UTC (start of day)
        $result = $method->invoke($filter, '2024-06-15', 'Asia/Shanghai', 'UTC', true);
        expect($result)->toBe('2024-06-14 16:00:00');

        // 2024-06-15 end of day in Shanghai = 2024-06-15 15:59:59 UTC
        $result = $method->invoke($filter, '2024-06-15', 'Asia/Shanghai', 'UTC', false);
        expect($result)->toBe('2024-06-15 15:59:59');
    });

    it('handles same timezone without offset', function () {
        $filter = TimezoneAwareDateFilter::make('created_at')
            ->userTimezone('UTC')
            ->databaseTimezone('UTC');

        $ref = new ReflectionClass($filter);
        $method = $ref->getMethod('convertToDbTimezone');
        $method->setAccessible(true);

        $result = $method->invoke($filter, '2024-06-15', 'UTC', 'UTC', true);
        expect($result)->toBe('2024-06-15 00:00:00');
    });
});
