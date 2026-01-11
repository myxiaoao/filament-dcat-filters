# JsonFilter

Query JSON/JSONB columns with path access and comparison operators.

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\JsonFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            JsonFilter::make('metadata')
                ->path('settings.theme')
                ->eq(),
        ]);
}
```

## JSON Path Access

### Dot Notation

```php
JsonFilter::make('metadata')
    ->path('settings.theme')
```

Generates: `metadata->settings->theme`

### Arrow Notation

```php
JsonFilter::make('metadata')
    ->path('settings->theme->color')
```

### Nested Paths

```php
JsonFilter::make('preferences')
    ->path('display.mode.dark')
```

## Comparison Operators

### Equals (Default)

```php
JsonFilter::make('settings')
    ->path('status')
    ->eq()
```

### Not Equals

```php
JsonFilter::make('settings')
    ->path('status')
    ->neq()
```

### Greater Than / Less Than

```php
JsonFilter::make('metadata')
    ->path('count')
    ->gt()  // Greater than

JsonFilter::make('metadata')
    ->path('count')
    ->gte() // Greater than or equal

JsonFilter::make('metadata')
    ->path('count')
    ->lt()  // Less than

JsonFilter::make('metadata')
    ->path('count')
    ->lte() // Less than or equal
```

### Like / Not Like

```php
JsonFilter::make('metadata')
    ->path('description')
    ->like()

JsonFilter::make('metadata')
    ->path('description')
    ->notLike()
```

### Custom Operator

```php
JsonFilter::make('metadata')
    ->path('value')
    ->operator('>=')
```

## Default Value

```php
JsonFilter::make('settings')
    ->path('theme')
    ->defaultValue('dark')
```

## Complete Example

```php
JsonFilter::make('user_preferences')
    ->label('Theme Preference')
    ->path('preferences.theme')
    ->eq()
    ->defaultValue('light')
    ->columnSpan(1),
```

## Supported Operators

| Operator | Method | Description |
|----------|--------|-------------|
| `=` | `eq()` | Equal to |
| `!=` | `neq()` | Not equal to |
| `>` | `gt()` | Greater than |
| `>=` | `gte()` | Greater than or equal |
| `<` | `lt()` | Less than |
| `<=` | `lte()` | Less than or equal |
| `like` | `like()` | Contains (with wildcards) |
| `not like` | `notLike()` | Does not contain |

## Database Compatibility

This filter uses the `->` JSON accessor syntax which is supported by:
- MySQL 5.7+
- MariaDB 10.2+
- PostgreSQL 9.3+
- SQLite 3.38+
