# Aggregate Filter

Filter records by aggregate values of related records, such as "users with more than 3 orders" or "products with average rating above 4.0".

## Basic Usage

### Count

```php
use Cooper\FilamentDcatFilters\Filters\AggregateFilter;

AggregateFilter::make('order_count')
    ->relationship('orders')
    ->count()
    ->gte()
```

### Sum

```php
AggregateFilter::make('total_revenue')
    ->relationship('orders')
    ->sum('amount')
    ->gte()
```

### Average, Max, Min

```php
->avg('rating')    // average
->max('price')     // maximum
->min('price')     // minimum
```

## Quick Factories

```php
AggregateFilter::countOf('orders')->gte()
AggregateFilter::sumOf('orders', 'amount')->gte()
```

## With Constraints

Filter by aggregate of specific related records:

```php
AggregateFilter::make('completed_orders')
    ->relationship('orders')
    ->count()
    ->constrainedBy(fn (Builder $q) => $q->where('status', 'completed'))
    ->gte()
```

## Operators

All standard comparison operators are supported:

```php
->gt()     // >
->gte()    // >=  (default)
->lt()     // <
->lte()    // <=
->eq()     // =
->ne()     // !=
```

## How It Works

The filter uses Laravel's `withCount` / `withSum` / `withAvg` / `withMax` / `withMin` methods combined with a `having` clause:

```php
// "Orders count >= 3"
$query->withCount('orders')->having('orders_count', '>=', 3)

// "Orders total amount >= 1000"
$query->withSum('orders', 'amount')->having('orders_sum_amount', '>=', 1000)
```

## API Reference

| Method | Description |
|--------|-------------|
| `relationship($name)` | Set the relationship to aggregate |
| `count()` | Use COUNT aggregate |
| `sum($column)` | Use SUM aggregate |
| `avg($column)` | Use AVG aggregate |
| `max($column)` | Use MAX aggregate |
| `min($column)` | Use MIN aggregate |
| `constrainedBy($closure)` | Add constraint to the relationship |
| `gt()` / `gte()` / `lt()` / `lte()` / `eq()` / `ne()` | Set comparison operator |
| `countOf($relationship)` | Static factory for count |
| `sumOf($relationship, $column)` | Static factory for sum |
