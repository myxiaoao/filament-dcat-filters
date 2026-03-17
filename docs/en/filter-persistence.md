# Filter State Persistence

This package provides two methods for persisting filter state: server-side session persistence and client-side LocalStorage persistence.

## Session Persistence

Session persistence stores filter state on the server, surviving page refreshes within the same session.

### Basic Usage

Add the `PersistsFiltersInSession` trait to your Livewire component:

```php
use Cooper\FilamentDcatFilters\Concerns\PersistsFiltersInSession;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use PersistsFiltersInSession;

    // Filters will automatically be restored when the page loads
    // and saved when filters change
}
```

### How It Works

1. When the component mounts, filters are restored from the session
2. When filters change (`updatedTableFilters`), they are saved to the session
3. The session key is unique per component class

### Manual Control

You can manually control the persistence:

```php
// Save current filters
$this->saveFiltersToSession();

// Restore filters
$this->restoreFiltersFromSession();

// Clear saved filters
$this->clearFiltersFromSession();
```

### Custom Session Key

Override the method to customize the session key:

```php
protected function getFilterSessionKey(): string
{
    return 'my-custom-filters:' . auth()->id();
}
```

## LocalStorage Persistence

LocalStorage persistence stores filter state in the browser, surviving session expiry.

### Basic Usage

Add the `PersistsFiltersInLocalStorage` trait:

```php
use Cooper\FilamentDcatFilters\Concerns\PersistsFiltersInLocalStorage;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use PersistsFiltersInLocalStorage;

    public function mount(): void
    {
        parent::mount();

        // Initialize LocalStorage persistence
        $this->initLocalStoragePersistence();
    }
}
```

### Include the JavaScript

Add the JavaScript file to your layout or Filament panel:

```html
<!-- In your layout -->
<script src="{{ asset('vendor/filament-dcat-filters/js/filter-persistence.js') }}"></script>
```

Or publish and import it in your build:

```bash
php artisan vendor:publish --tag=filament-dcat-filters-assets
```

### JavaScript API

The package provides a global JavaScript API:

```javascript
// Save filters manually
FilamentDcatFilters.saveFilters('my-key', { status: 'active' });

// Load filters
const filters = FilamentDcatFilters.loadFilters('my-key');

// Clear specific filters
FilamentDcatFilters.clearFilters('my-key');

// Clear all filter persistence
FilamentDcatFilters.clearFilters();
```

## Configuration

Configure persistence behavior in `config/filament-dcat-filters.php`:

```php
'persistence' => [
    // Session key prefix
    'session_prefix' => 'filament-dcat-filters',

    // Enable LocalStorage persistence by default
    'local_storage_enabled' => false,

    // LocalStorage key prefix
    'local_storage_prefix' => 'filament-dcat-filters',

    // Automatically clear persistence on filter reset
    'clear_on_reset' => true,
],
```

## Combining Both Methods

You can use both session and LocalStorage persistence together:

```php
use Cooper\FilamentDcatFilters\Concerns\PersistsFiltersInSession;
use Cooper\FilamentDcatFilters\Concerns\PersistsFiltersInLocalStorage;

class ListPosts extends ListRecords
{
    use PersistsFiltersInSession;
    use PersistsFiltersInLocalStorage;

    public function mount(): void
    {
        parent::mount();

        // Session takes priority, LocalStorage as fallback
        if (empty($this->tableFilters)) {
            $this->initLocalStoragePersistence();
        }
    }
}
```

## Clearing Persistence on Reset

When using the `ResetFiltersAction`, persistence is automatically cleared if configured:

```php
ResetFiltersAction::make()
    ->afterReset(function ($livewire) {
        // Session is automatically cleared if trait is used
        if (method_exists($livewire, 'clearFiltersFromSession')) {
            $livewire->clearFiltersFromSession();
        }
    });
```

## User-Specific Persistence

For user-specific filter preferences, customize the session key:

```php
protected function getFilterSessionKey(): string
{
    $userId = auth()->id() ?? 'guest';
    return "filament-dcat-filters:{$userId}:" . static::class;
}
```

## Events

The following Livewire events are dispatched:

- `filament-dcat-filters::init-local-storage` - Initializes LocalStorage persistence
- `filament-dcat-filters::restore-from-local-storage` - Requests filter restoration
- `filament-dcat-filters::clear-local-storage` - Clears LocalStorage
- `filament-dcat-filters::filters-reset` - Dispatched when filters are reset

Listen to these events in your JavaScript:

```javascript
Livewire.on('filament-dcat-filters::filters-reset', () => {
    console.log('Filters have been reset');
});
```
