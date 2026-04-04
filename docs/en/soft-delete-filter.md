# SoftDelete Filter

Control visibility of soft-deleted records with a single line of code.

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\SoftDeleteFilter;

SoftDeleteFilter::make('trashed')
```

This creates a three-state dropdown: All / Without Trashed / With Trashed / Only Trashed.

## Display Modes

### Select (Default)

```php
SoftDeleteFilter::make('trashed')           // dropdown select
```

### Radio Buttons

```php
SoftDeleteFilter::make('trashed')->radio()
```

### Toggle

Binary toggle — off = without trashed, on = with trashed (no "only trashed" option):

```php
SoftDeleteFilter::make('trashed')->toggle()
```

## Custom Labels

```php
SoftDeleteFilter::make('trashed')
    ->withoutTrashedLabel('Active')
    ->onlyTrashedLabel('Deleted')
    ->withTrashedLabel('All Records')
```

## How It Works

| Value | Query Effect |
|-------|-------------|
| (empty) | No modification (Laravel default: excludes trashed) |
| `with` | `$query->withTrashed()` |
| `only` | `$query->onlyTrashed()` |

## Requirements

Your model must use the `SoftDeletes` trait:

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
}
```

## API Reference

| Method | Description |
|--------|-------------|
| `toggle()` | Use binary toggle mode |
| `radio()` | Use radio button display |
| `select()` | Use dropdown select (default) |
| `withoutTrashedLabel($label)` | Customize "without trashed" label |
| `onlyTrashedLabel($label)` | Customize "only trashed" label |
| `withTrashedLabel($label)` | Customize "with trashed" label |
