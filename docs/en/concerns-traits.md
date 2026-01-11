# Concerns (Traits)

This package provides several traits that can be used in your Filament ListRecords classes to add additional functionality.

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
