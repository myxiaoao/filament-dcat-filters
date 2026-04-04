# Advanced JSON Filter

Structural JSON queries beyond simple path-value comparison. Supports array contains, path exists, and key exists operations.

## Basic Usage

### Array Contains

Check if a JSON array column contains a specific value:

```php
use Cooper\FilamentDcatFilters\Filters\AdvancedJsonFilter;

AdvancedJsonFilter::make('tags')
    ->arrayContains()
```

### With Predefined Options

```php
AdvancedJsonFilter::make('tags')
    ->arrayContains()
    ->options(['php' => 'PHP', 'js' => 'JavaScript', 'go' => 'Go'])
    ->multiple()
```

### Path Exists

Check if a specific path exists in a JSON column:

```php
AdvancedJsonFilter::make('metadata')
    ->pathExists('settings.theme')
```

### Has Key

Check if a top-level key exists in a JSON object:

```php
AdvancedJsonFilter::make('config')
    ->hasKey('notifications')
```

## How It Works

| Mode | MySQL | PostgreSQL | SQLite |
|------|-------|-----------|--------|
| arrayContains | `JSON_CONTAINS()` | `@> ::jsonb` | `json_each()` (degraded) |
| pathExists | `JSON_CONTAINS_PATH()` | `jsonb_path_exists()` | Not supported |
| hasKey | `JSON_CONTAINS_PATH()` | `? operator` | Not supported |

## Database Support

- **MySQL, PostgreSQL**: Full support for all modes
- **SQLite**: Only `arrayContains` works (via `json_each` subquery). `pathExists` and `hasKey` throw `UnsupportedDatabaseDriverException`

## API Reference

| Method | Description |
|--------|-------------|
| `arrayContains()` | Set array contains mode |
| `pathExists($path)` | Set path exists mode |
| `hasKey($key)` | Set has key mode |
| `options($array)` | Predefined options for select |
| `multiple()` | Enable multiple selection |

## See Also

- [JSON Filter](json-filter.md) — For simple path-value comparisons
