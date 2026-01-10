# Reset Filters Action

The Reset Filters Action provides a convenient way to clear all active table filters with a single click.

## Basic Usage

Add the reset filters action to your table's header actions:

```php
use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->filters([...])
        ->headerActions([
            ResetFiltersAction::make(),
        ]);
}
```

Or use the helper function:

```php
use function resetFiltersAction;

public function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->filters([...])
        ->headerActions([
            resetFiltersAction(),
        ]);
}
```

## Features

### Auto-Hide When Empty

By default, the reset button is only visible when there are active filters. You can disable this behavior:

```php
ResetFiltersAction::make()
    ->autoHideWhenEmpty(false)
```

### Confirmation Modal

You can require user confirmation before resetting filters:

```php
ResetFiltersAction::make()
    ->withConfirmation()
```

This will show a confirmation modal with customizable text.

### Custom Styling

Customize the button appearance:

```php
ResetFiltersAction::make()
    ->label('Clear All')
    ->icon('heroicon-o-trash')
    ->color('danger')
    ->size('lg')
```

### After Reset Callback

Execute code after filters are reset:

```php
ResetFiltersAction::make()
    ->afterReset(function ($livewire) {
        // Custom logic after reset
        $livewire->dispatch('filters-cleared');
    })
```

## Using the Trait

For automatic integration, use the `HasResetFilters` trait:

```php
use Cooper\FilamentDcatFilters\Concerns\HasResetFilters;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use HasResetFilters;

    public function table(Table $table): Table
    {
        return $this->withResetFiltersAction(
            $table
                ->columns([...])
                ->filters([...])
        );
    }
}
```

## Events

The action dispatches a `filament-dcat-filters::filters-reset` event after resetting filters. You can listen to this event in your JavaScript:

```javascript
document.addEventListener('livewire:init', () => {
    Livewire.on('filament-dcat-filters::filters-reset', () => {
        console.log('Filters have been reset');
    });
});
```

## Customization

### Custom Label

```php
ResetFiltersAction::make()
    ->label(__('Clear All Filters'))
```

### Custom Icon

```php
use Filament\Support\Icons\Heroicon;

ResetFiltersAction::make()
    ->icon(Heroicon::OutlineTrash)
```

### Visibility Control

Control when the button is visible:

```php
ResetFiltersAction::make()
    ->visible(fn ($livewire) => count($livewire->tableFilters) > 2)
```

## Complete Example

```php
use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;
use Cooper\FilamentDcatFilters\Filters\RangeFilter;
use Cooper\FilamentDcatFilters\Filters\ScopeFilter;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('title'),
            TextColumn::make('status'),
            TextColumn::make('created_at'),
        ])
        ->filters([
            ScopeFilter::forStatus('status', [
                'draft' => 'Draft',
                'published' => 'Published',
            ]),
            RangeFilter::make('created_at')->date(),
        ])
        ->headerActions([
            ResetFiltersAction::make()
                ->withConfirmation()
                ->afterReset(function ($livewire) {
                    // Optional: notify user
                    Notification::make()
                        ->title('Filters cleared')
                        ->success()
                        ->send();
                }),
        ]);
}
```
