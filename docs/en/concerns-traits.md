# Concerns (Traits)

This package provides several traits that can be used in your filter classes and Filament ListRecords classes to add additional functionality.

## HasLabelResolver

Provides a unified label resolution mechanism for filters that need to display a human-readable label. It consolidates what previously required 3 different label resolution variants across the codebase into a single reusable trait.

### Available Methods

```php
// Resolve the display label for this filter
// Returns the filter's configured label if set, otherwise generates one from the filter name
// e.g. 'created_at' becomes 'Created at'
$label = $this->resolveLabel();

// Returns a Closure that resolves the label (useful for deferred evaluation)
$resolver = $this->labelResolver();
```

### Filters Using This Trait

All filters that display a label in the UI use this trait, including LikeFilter, InFilter, ComparisonFilter, BetweenFilter, BooleanFilter, NullFilter, EnumFilter, FullTextFilter, RangeFilter, RelativeDateFilter, and others.

### Background

Previously, label resolution logic was duplicated across multiple filters with slight variations. `HasLabelResolver` centralises this into two methods (`resolveLabel()` and `labelResolver()`) so every filter resolves labels consistently.

---

## HasColumnName

Allows the filter name to differ from the actual database column name. This is useful when you want a semantic filter name in the URL or form state while querying a different column.

### Usage

```php
use Cooper\FilamentDcatFilters\Filters\LikeFilter;

// Filter name is 'search_title', but queries the 'title' column
LikeFilter::make('search_title')
    ->column('title');
```

### Available Methods

```php
// Set the database column name
$filter->column('actual_column_name');

// Resolve the column name (used internally by filters)
// Returns the custom column name if set, otherwise falls back to the filter name
$column = $filter->resolveColumnName();
```

### Filters Using This Trait

ComparisonFilter, LikeFilter, InFilter, RangeFilter, EnumFilter, DateComponentFilter, RelativeDateFilter, RegexFilter, HiddenFilter, SelectTableFilter, FindInSetFilter, JsonFilter

---

## HasOperator

Provides a unified set of comparison operator methods. The using class must define an `ALLOWED_OPERATORS` constant that lists valid operators.

### Usage

```php
use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;

ComparisonFilter::make('price')->gt();   // WHERE price > ?
ComparisonFilter::make('stock')->lte();  // WHERE stock <= ?
ComparisonFilter::make('age')->operator('!='); // WHERE age != ?
```

### Available Methods

```php
$filter->gt();    // Set operator to >
$filter->gte();   // Set operator to >=
$filter->lt();    // Set operator to <
$filter->lte();   // Set operator to <=
$filter->eq();    // Set operator to =
$filter->ne();    // Set operator to !=
$filter->operator(string $operator); // Set a custom operator (validated against ALLOWED_OPERATORS)
```

### Filters Using This Trait

ComparisonFilter, HiddenFilter

### Requirements

The class using this trait must define an `ALLOWED_OPERATORS` constant:

```php
const ALLOWED_OPERATORS = ['=', '!=', '>', '>=', '<', '<='];
```

An `InvalidArgumentException` is thrown if an unsupported operator is passed.

---

## HasFilterPresets

Save and load filter combinations for quick access.

### Setup

```php
use Cooper\FilamentDcatFilters\Concerns\HasFilterPresets;

class ListOrders extends ListRecords
{
    use HasFilterPresets;

    protected function getFilterPresets(): array
    {
        return [
            'pending_orders' => [
                'label' => 'Pending Orders',
                'filters' => ['status' => 'pending', 'payment' => 'unpaid'],
                'icon' => 'heroicon-o-clock',
                'color' => 'warning',
            ],
            'high_value' => [
                'label' => 'High Value Orders',
                'filters' => ['total' => ['from' => 1000]],
                'icon' => 'heroicon-o-currency-dollar',
                'color' => 'success',
            ],
        ];
    }
}
```

### Preset Configuration

| Key | Type | Description |
|-----|------|-------------|
| `label` | string | Display name for the preset |
| `filters` | array | Filter values to apply |
| `icon` | string | Optional Heroicon name |
| `color` | string | Optional color (gray, primary, success, warning, danger) |

### Available Methods

```php
// Get actions for the table header
$actions = $this->getFilterPresetActions();

// Apply a preset programmatically
$this->applyFilterPreset(['status' => 'active']);

// Check if a preset is currently active
$isActive = $this->isFilterPresetActive('pending_orders');

// Get the currently active preset key
$activePreset = $this->getActiveFilterPreset();

// Reset all filters
$this->resetFilterPresets();
```

---

## HasScopeBadgeCounts

Display record counts on scope filter tabs.

### Setup

```php
use Cooper\FilamentDcatFilters\Concerns\HasScopeBadgeCounts;

class ListPosts extends ListRecords
{
    use HasScopeBadgeCounts;

    public function mount(): void
    {
        parent::mount();

        $this->registerScopesForBadgeCounts([
            'all' => [],
            'published' => [
                'query' => fn ($q) => $q->where('status', 'published'),
            ],
            'draft' => [
                'query' => fn ($q) => $q->where('status', 'draft'),
            ],
        ]);
    }

    protected function getBaseQueryForScopeCounting(): Builder
    {
        return Post::query();
    }
}
```

### Available Methods

```php
// Get count for a specific scope
$count = $this->getScopeBadgeCount('published');

// Get all scope counts
$counts = $this->getAllScopeBadgeCounts();

// Enable/disable badge counts
$this->scopeBadgeCounts(false);

// Check if enabled
$enabled = $this->areScopeBadgeCountsEnabled();

// Format large numbers (1000 → 1K, 1500000 → 1.5M)
$formatted = $this->formatScopeBadgeCount(1500);

// Clear cache
$this->clearScopeBadgeCountCache();

// Refresh a specific scope's count
$this->refreshScopeBadgeCount('published');
```

---

## HasFilterExportImport

Export and import filter configurations for sharing or persistence.

### Setup

```php
use Cooper\FilamentDcatFilters\Concerns\HasFilterExportImport;

class ListOrders extends ListRecords
{
    use HasFilterExportImport;
}
```

### Export Filters

```php
// Export as JSON string
$json = $this->exportFilters();

// Export with formatting
$prettyJson = $this->exportFilters(formatted: true);

// Export as base64 (URL-safe)
$base64 = $this->exportFiltersAsBase64();

// Get as array
$data = $this->getFilterExportData();
```

### Import Filters

```php
// Import from JSON
$success = $this->importFilters($jsonString);

// Import from base64
$success = $this->importFiltersFromBase64($base64String);

// Merge with existing (overwrite conflicts)
$success = $this->mergeFilters($jsonString, overwrite: true);

// Merge without overwriting
$success = $this->mergeFilters($jsonString, overwrite: false);
```

### URL Sharing

```php
// Generate shareable URL
$url = $this->getFilterShareUrl();
// Result: https://example.com/orders?filters=eyJ2ZXJzaW9uIj...

// Load filters from URL (call in mount())
public function mount(): void
{
    parent::mount();
    $this->loadFiltersFromUrl();
}
```

### Encryption

```php
// Enable encryption for sensitive filter data
$this->encryptFilters(true);

$encrypted = $this->exportFilters();
// Result: encrypted string

// Import will auto-detect and decrypt
$this->importFilters($encrypted);
```

### Clear Filters

```php
$this->clearImportedFilters();
```

### Export Data Format

```json
{
    "version": "1.0",
    "timestamp": "2024-01-15T10:30:00+00:00",
    "filters": {
        "status": {"value": "active"},
        "date_range": {"from": "2024-01-01", "to": "2024-01-31"}
    }
}
```

---

## Combining Traits

You can use multiple traits together:

```php
use Cooper\FilamentDcatFilters\Concerns\HasFilterPresets;
use Cooper\FilamentDcatFilters\Concerns\HasScopeBadgeCounts;
use Cooper\FilamentDcatFilters\Concerns\HasFilterExportImport;

class ListOrders extends ListRecords
{
    use HasFilterPresets;
    use HasScopeBadgeCounts;
    use HasFilterExportImport;

    // Implement required methods...
}
```
