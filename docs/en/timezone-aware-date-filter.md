# Timezone Aware Date Filter

Date range filtering with automatic timezone conversion between user timezone and database timezone.

## Problem

When a user in UTC+8 selects "2024-06-15", the database (storing UTC) should query `2024-06-14 16:00:00` to `2024-06-15 15:59:59`. This filter handles the conversion automatically.

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\TimezoneAwareDateFilter;

TimezoneAwareDateFilter::make('created_at')
    ->userTimezone('Asia/Shanghai')
```

## Dynamic User Timezone

Read timezone from the authenticated user:

```php
TimezoneAwareDateFilter::make('created_at')
    ->userTimezone(fn () => auth()->user()?->timezone ?? 'UTC')
```

## Database Timezone

Defaults to `'UTC'`. Override if your database uses a different timezone:

```php
TimezoneAwareDateFilter::make('created_at')
    ->userTimezone('Asia/Shanghai')
    ->databaseTimezone('America/New_York')
```

## Datetime Mode

Include time selection:

```php
TimezoneAwareDateFilter::make('created_at')
    ->userTimezone('Asia/Shanghai')
    ->datetime()
```

## How It Works

1. User selects dates in their local timezone
2. PHP converts to database timezone using Carbon:
   - Date mode: `startOfDay()` / `endOfDay()` boundaries
   - Datetime mode: exact timestamps
3. `whereBetween` query uses converted values
4. Indicator displays dates in user timezone (not converted values)

## API Reference

| Method | Description |
|--------|-------------|
| `userTimezone($tz)` | Set user timezone (string or Closure) |
| `databaseTimezone($tz)` | Set database timezone (default: 'UTC') |
| `date()` | Date-only mode (default) |
| `datetime()` | Date + time mode |
