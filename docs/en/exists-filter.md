# Exists Filter

Filter records based on whether related records exist. For example: "articles with comments", "users without orders".

## Basic Usage

### Has Related Records

```php
use Cooper\FilamentDcatFilters\Filters\ExistsFilter;

ExistsFilter::make('has_comments')
    ->relationship('comments')
```

### Does Not Have Related Records

```php
ExistsFilter::make('no_orders')
    ->relationship('orders')
    ->notExists()
```

## Quick Factories

```php
ExistsFilter::forExists('comments')       // whereHas shortcut
ExistsFilter::forNotExists('orders')      // whereDoesntHave shortcut
```

## With Constraints

Filter by related records matching specific conditions:

```php
ExistsFilter::make('has_published_comments')
    ->relationship('comments')
    ->constrainedBy(fn (Builder $q) => $q->where('status', 'published'))
```

## Display Modes

### Toggle (Default)

Binary toggle — on applies the exists/not-exists check:

```php
ExistsFilter::make('has_comments')
    ->relationship('comments')
    ->toggle()
```

### Select

Three-state dropdown: All / Exists / Does Not Exist:

```php
ExistsFilter::make('has_comments')
    ->relationship('comments')
    ->select()
```

### Radio

```php
ExistsFilter::make('has_comments')
    ->relationship('comments')
    ->radio()
```

## Custom Labels

```php
ExistsFilter::make('has_comments')
    ->relationship('comments')
    ->existsLabel('Has comments')
    ->notExistsLabel('No comments')
    ->allLabel('All')
```

## How It Works

| Value | Query Effect |
|-------|-------------|
| (empty) | No modification |
| `exists` | `$query->whereHas($relationship, $constraint)` |
| `not_exists` | `$query->whereDoesntHave($relationship, $constraint)` |

## API Reference

| Method | Description |
|--------|-------------|
| `relationship($name)` | Set the relationship to check |
| `notExists()` | Invert to check for non-existence |
| `constrainedBy($closure)` | Add query constraint to the relationship check |
| `toggle()` | Use binary toggle mode (default) |
| `select()` | Use dropdown select |
| `radio()` | Use radio button display |
| `existsLabel($label)` | Customize "exists" label |
| `notExistsLabel($label)` | Customize "not exists" label |
| `allLabel($label)` | Customize "all" label |
| `forExists($relationship)` | Static factory for exists check |
| `forNotExists($relationship)` | Static factory for not-exists check |
