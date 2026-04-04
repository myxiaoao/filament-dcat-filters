# Column Compare Filter

Filter records by comparing two database columns, such as "price greater than cost" or "start date before end date".

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\ColumnCompareFilter;

ColumnCompareFilter::make('profitable')
    ->leftColumn('price')
    ->rightColumn('cost')
    ->gt()
```

## Quick Factory

```php
ColumnCompareFilter::comparing('price', 'cost')->gt()
```

## Display Modes

### Toggle (Default)

Binary toggle with a fixed operator:

```php
ColumnCompareFilter::make('profitable')
    ->leftColumn('price')
    ->rightColumn('cost')
    ->gt()
    ->toggle()
```

### Select

Dropdown where the user picks the operator:

```php
ColumnCompareFilter::make('date_check')
    ->leftColumn('start_at')
    ->rightColumn('end_at')
    ->select()
```

## Operators

```php
->gt()     // >
->gte()    // >=
->lt()     // <
->lte()    // <=
->eq()     // =
->ne()     // !=
```

## How It Works

Uses Laravel's `whereColumn` method:

```php
$query->whereColumn('price', '>', 'cost')
```

In select mode, the operator is validated against a whitelist to prevent SQL injection.

## API Reference

| Method | Description |
|--------|-------------|
| `leftColumn($column)` | Set the left column |
| `rightColumn($column)` | Set the right column |
| `comparing($left, $right)` | Static factory |
| `gt()` / `gte()` / `lt()` / `lte()` / `eq()` / `ne()` | Set comparison operator |
| `toggle()` | Use binary toggle mode (default) |
| `select()` | Use operator select dropdown |
