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

## HasDatabaseDriver

Detects and resolves the active database driver (MySQL, PostgreSQL, SQLite) to enable driver-specific SQL generation inside filters.

Resolution priority:
1. Filter-level override via `driver()`
2. Package config `filament-dcat-filters.database.driver`
3. Auto-detect from the query's connection

### Available Methods

| Method | Description |
|--------|-------------|
| `driver(string $driver): static` | Manually override the driver for this filter instance |
| `resolveDriver(Builder $query): string` | Resolve the effective driver name |
| `isPostgres(Builder $query): bool` | Returns `true` when the resolved driver is `pgsql` |

### Filters Using This Trait

FullTextFilter, FindInSetFilter, JsonFilter, RegexFilter

---

## HasInlineLabel

Provides Dcat Admin-style inline label display, where the label is rendered as a prefix inside the input element rather than above it. Controlled globally via config or per-filter via `inlineLabel()`.

### Available Methods

| Method | Description |
|--------|-------------|
| `inlineLabel(bool $condition = true): static` | Enable or disable inline label for this filter |
| `placeholderFromLabel(bool $condition = true): static` | Use the label text as the input placeholder |
| `shouldInlineLabel(): bool` | Resolve whether inline label is active (respects config default) |
| `shouldPlaceholderFromLabel(): bool` | Resolve whether placeholder-from-label is active |
| `applyInlineLabel(Component $component, string\|Closure $label): Component` | Apply inline label to a single form component |
| `applyRangeInlineLabels(Component $from, Component $to, string\|Closure $label): void` | Apply inline labels to a from/to range pair |

### Filters Using This Trait

LikeFilter, ComparisonFilter, InFilter, EnumFilter, BooleanFilter, NullFilter, RangeFilter, RelativeDateFilter, BetweenFilter, and most other filters that render form inputs.

---

## HasRangeQuery

Encapsulates range query logic (from/to) used by range-style filters. Handles empty-value detection that correctly treats `"0"` as a valid non-empty value, and auto-swaps `from`/`to` when they are reversed.

### Available Methods

| Method | Description |
|--------|-------------|
| `isRangeValueEmpty(mixed $value): bool` | Returns `true` only for `null` or `""` — treats `0` as a valid value |
| `applyRangeQuery(Builder $query, string $column, array $data): Builder` | Applies `>=`, `<=`, or `BETWEEN` constraints based on which values are present |
| `generateRangeIndicators(array $data, string $label): array` | Returns an array of active-filter indicator strings for the from/to values |

### Filters Using This Trait

RangeFilter, BetweenFilter, DateComponentFilter

---

## HasRelationship

Adds Eloquent relationship support to filters, allowing them to query through `whereHas` instead of directly on the model's own columns.

### Usage

```php
// Single-level relationship
LikeFilter::make('tag_name')
    ->relationship('tags', 'name');

// Nested relationship (deep path) — automatically uses Laravel's nested whereHas
LikeFilter::make('country_name')
    ->relationship('author.company.country', 'name');
```

### Available Methods

| Method | Description |
|--------|-------------|
| `relationship(string $name, ?string $titleColumn = null): static` | Configure the relationship name and optional title column |
| `hasRelationship(): bool` | Check whether a relationship has been configured |
| `applyRelationshipConstraint(Builder $query, string $column, string $operator, mixed $value): Builder` | Apply a single-value `whereHas` constraint |
| `applyRelationshipWhereIn(Builder $query, string $column, array $values, bool $negate = false): Builder` | Apply a multi-value `whereHas` + `whereIn`/`whereNotIn` constraint |

### Filters Using This Trait

LikeFilter, InFilter, ComparisonFilter, EnumFilter

---

## HasSelectRadioDisplay

Provides a unified builder for Select dropdown and Radio button form components. The default display style is `select`; calling `radio()` switches to inline radio buttons.

### Available Methods

| Method | Description |
|--------|-------------|
| `radio(): static` | Switch to radio button display |
| `select(): static` | Switch to select dropdown display (default) |
| `columns(array\|int\|null $columns = 3): static` | Set the number of columns for the radio layout |
| `buildFormComponent(string $fieldName, Closure $labelResolver, array $options, string $placeholder): Select\|Radio` | Build the appropriate component based on current display style |

### Filters Using This Trait

InFilter, EnumFilter, BooleanFilter

---

## HasResetFilters

Provides a one-click reset action to clear all active filters. Renders a header action button on the table.

> For detailed usage, see [reset-filters.md](reset-filters.md).

---

## PersistsFiltersInLocalStorage

Persists table filter state in the browser's LocalStorage, so filters survive session expiry and page reloads without server-side storage.

> For detailed usage, see [filter-persistence.md](filter-persistence.md).

### Available Methods

| Method | Description |
|--------|-------------|
| `getLocalStorageKey(): string` | Returns the LocalStorage key scoped to the component class |
| `initLocalStoragePersistence(): void` | Dispatches the JS event to initialize persistence |
| `mountPersistsFiltersInLocalStorage(): void` | Livewire mount hook — dispatches restore event |
| `restoreFiltersFromLocalStorage(array $filters): void` | Livewire listener — applies restored filters |

---

## PersistsFiltersInSession

Persists table filter state in the server-side Laravel session, so filters survive page refreshes within the same session.

> For detailed usage, see [filter-persistence.md](filter-persistence.md).

### Available Methods

| Method | Description |
|--------|-------------|
| `getFilterSessionKey(): string` | Returns the session key scoped to the component class |
| `bootPersistsFiltersInSession(): void` | Livewire boot hook — restores filters from session |
| `restoreFiltersFromSession(): void` | Reads saved filters from session and applies them |
| `saveFiltersToSession(): void` | Writes current filters to session |
| `clearFiltersFromSession(): void` | Removes saved filters from session |
| `updatedTableFilters(): void` | Livewire hook — auto-saves filters whenever they change |

---

## SyncsFiltersToUrl

Syncs table filter state (filters, search, sort column, sort direction) to the browser URL using Livewire's query string feature. Browser history entries are created on each change, enabling the back button to restore previous filter states.

> For detailed usage, see [url-sync.md](url-sync.md).

### Available Methods

| Method | Description |
|--------|-------------|
| `queryString(): array` | Returns Livewire query string config with `history: true` |
| `getFilterQueryString(): array` | Returns the current filter state as a plain array for manual URL building |
| `getShareableFilterUrl(): string` | Builds a full URL with the current filter state as query parameters |
| `resetUrlParameters(): void` | Clears all synced URL parameters |

---

## SyncsFiltersToUrlWithoutHistory

Identical to `SyncsFiltersToUrl` but sets `history: false` — the URL is updated via `replaceState` instead of `pushState`, so no browser history entries are created.

> For detailed usage, see [url-sync.md](url-sync.md).

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
