# URL Query Parameter Sync

Sync filter state with browser URL parameters to enable bookmarking and sharing filtered views.

## Basic Usage

Add the `SyncsFiltersToUrl` trait to your Livewire component:

```php
use Cooper\FilamentDcatFilters\Concerns\SyncsFiltersToUrl;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use SyncsFiltersToUrl;

    // Filters, search, and sort will automatically sync to URL
}
```

## What Gets Synced

The trait syncs the following properties to the URL:

- `tableFilters` - All active filter values
- `tableSearch` - Search query
- `tableSortColumn` - Sort column name
- `tableSortDirection` - Sort direction (asc/desc)

## URL Format

The URL will look like:

```
/posts?tableFilters[status][value]=published&tableSearch=hello&tableSortColumn=created_at&tableSortDirection=desc
```

## History Mode

### With Browser History (Default)

Use `SyncsFiltersToUrl` to push each filter change to browser history:

```php
use Cooper\FilamentDcatFilters\Concerns\SyncsFiltersToUrl;

class ListPosts extends ListRecords
{
    use SyncsFiltersToUrl;
}
```

Users can use the browser back button to navigate through filter states.

### Without Browser History

Use `SyncsFiltersToUrlWithoutHistory` to update URL without creating history entries:

```php
use Cooper\FilamentDcatFilters\Concerns\SyncsFiltersToUrlWithoutHistory;

class ListPosts extends ListRecords
{
    use SyncsFiltersToUrlWithoutHistory;
}
```

## Shareable URLs

Generate a shareable URL with current filter state:

```php
// In your Livewire component
$shareUrl = $this->getShareableFilterUrl();

// Returns: https://example.com/posts?tableFilters[status][value]=published
```

## Get Query String

Get the filter query string as an array:

```php
$query = $this->getFilterQueryString();

// Returns: [
//     'tableFilters' => ['status' => ['value' => 'published']],
//     'tableSearch' => 'hello',
// ]
```

## Resetting URL

Clear all URL parameters:

```php
$this->resetUrlParameters();
```

This also works well with `ResetFiltersAction`:

```php
ResetFiltersAction::make()
    ->afterReset(function ($livewire) {
        if (method_exists($livewire, 'resetUrlParameters')) {
            $livewire->resetUrlParameters();
        }
    });
```

## Custom URL Parameters

If you need to customize which properties are synced, you can override them:

```php
use Livewire\Attributes\Url;

class ListPosts extends ListRecords
{
    // Only sync filters (not search or sort)
    #[Url(except: [])]
    public array $tableFilters = [];

    // Use custom URL parameter name
    #[Url(as: 'q')]
    public ?string $tableSearch = null;

    // Don't sync sort to URL
    public ?string $tableSortColumn = null;
    public ?string $tableSortDirection = null;
}
```

## Selective Syncing

Create a custom trait for selective syncing:

```php
namespace App\Concerns;

use Livewire\Attributes\Url;

trait SyncsOnlyFiltersToUrl
{
    #[Url(except: [], history: true)]
    public array $tableFilters = [];
}
```

## Initial State from URL

When the page loads with URL parameters, Livewire automatically hydrates the properties:

```php
// URL: /posts?tableFilters[status][value]=draft

class ListPosts extends ListRecords
{
    use SyncsFiltersToUrl;

    public function mount(): void
    {
        parent::mount();

        // $this->tableFilters is already populated from URL
        // ['status' => ['value' => 'draft']]
    }
}
```

## Security Considerations

URL parameters are user-controllable. Filament's filter system already validates and sanitizes filter values, but be aware that:

1. Filter values come from user input
2. Only valid filter configurations will be applied
3. Invalid filter data is ignored by Filament

## Complete Example

```php
use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;
use Cooper\FilamentDcatFilters\Concerns\SyncsFiltersToUrl;
use Cooper\FilamentDcatFilters\Filters\ScopeFilter;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use SyncsFiltersToUrl;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('status'),
            ])
            ->filters([
                ScopeFilter::forStatus('status', [
                    'draft' => 'Draft',
                    'published' => 'Published',
                ]),
            ])
            ->headerActions([
                ResetFiltersAction::make()
                    ->afterReset(fn ($livewire) => $livewire->resetUrlParameters()),
            ]);
    }
}
```
