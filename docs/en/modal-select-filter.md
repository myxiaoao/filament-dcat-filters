# Modal Select Filter (Dcat Admin Style)

The **ModalSelectFilter** is a Dcat Admin-inspired filter that provides a modal dialog with a full table for selection. This is different from the existing SelectTableFilter which uses a dropdown select component.

## Overview

Inspired by Dcat Admin's `SelectTable` field, this filter opens a modal dialog containing a complete table where users can browse, search, and select records. It's ideal when you need:

- Visual browsing of records in a table format
- Complex selection with multiple columns displayed
- Better UX for selecting from large datasets
- Single or multiple record selection

## Features

- 🎯 **Modal Dialog** - Full-screen modal with customizable width
- 📊 **Table Display** - Show multiple columns with sorting and pagination
- 🔍 **Searchable** - Define which columns are searchable
- ✅ **Single/Multiple Selection** - Support for both selection modes
- 🎨 **Customizable** - Configure displayed columns, dialog size, and more
- 📱 **Responsive** - Mobile-friendly design

## Basic Usage

### Simple Model Selection

```php
use Cooper\FilamentDcatFilters\Filters\ModalSelectFilter;

ModalSelectFilter::make('user_id')
    ->label('User')
    ->model(User::class, 'name', 'id')
    ->dialogTitle('Select User')
```

### With Multiple Selection

```php
ModalSelectFilter::make('category_ids')
    ->label('Categories')
    ->model(Category::class, 'name', 'id')
    ->multiple()
    ->dialogTitle('Select Categories')
    ->dialogWidth('1000px')
```

### With Relationship

When using `relationship()`, you should pass the related model class so the modal table and label fetching work correctly:

```php
// Recommended: pass model class directly in relationship()
ModalSelectFilter::make('author_id')
    ->label('Author')
    ->relationship('author', 'name', 'id', User::class)
    ->dialogTitle('Select Author')

// Or set model() first, then relationship()
ModalSelectFilter::make('author_id')
    ->label('Author')
    ->model(User::class, 'name', 'id')
    ->relationship('author')
    ->dialogTitle('Select Author')
```

## Advanced Configuration

### Custom Display Columns

Define which columns to display in the modal table:

```php
ModalSelectFilter::make('user_id')
    ->label('User')
    ->model(User::class, 'name', 'id')
    ->displayColumns([
        'id' => 'ID',
        'name' => 'Name',
        'email' => 'Email',
        'created_at' => 'Registered',
    ])
    ->dialogTitle('Select User')
```

### Searchable Columns

Specify which columns should be searchable:

```php
ModalSelectFilter::make('user_id')
    ->label('User')
    ->model(User::class, 'name', 'id')
    ->displayColumns([
        'id' => 'ID',
        'name' => 'Name',
        'email' => 'Email',
    ])
    ->searchable(['name', 'email'])
    ->dialogTitle('Select User')
```

### Custom Query Modification

Modify the query used to fetch records:

```php
ModalSelectFilter::make('user_id')
    ->label('Active Users')
    ->model(User::class, 'name', 'id')
    ->modifyQueryUsing(function ($query) {
        return $query->where('is_active', true)
            ->where('role', 'member');
    })
    ->dialogTitle('Select Active User')
```

### Dialog Customization

Customize the modal dialog appearance:

```php
ModalSelectFilter::make('product_id')
    ->label('Product')
    ->model(Product::class, 'name', 'id')
    ->dialogTitle('Choose a Product')
    ->dialogWidth('80%')  // Can use px, %, or viewport units
    ->displayColumns([
        'sku' => 'SKU',
        'name' => 'Product Name',
        'price' => 'Price',
        'stock' => 'Stock',
    ])
```

## Complete Example

Here's a complete example demonstrating all features:

```php
use Cooper\FilamentDcatFilters\Filters\ModalSelectFilter;
use App\Models\Product;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('order_number'),
            TextColumn::make('product.name'),
            TextColumn::make('total'),
        ])
        ->filters([
            ModalSelectFilter::make('product_id')
                ->label('Product')
                ->model(Product::class, 'name', 'id')
                ->multiple()
                ->dialogTitle('Select Products')
                ->dialogWidth('1000px')
                ->displayColumns([
                    'id' => 'ID',
                    'sku' => 'SKU',
                    'name' => 'Product Name',
                    'category.name' => 'Category',
                    'price' => 'Price',
                    'stock' => 'In Stock',
                ])
                ->searchable(['name', 'sku', 'category.name'])
                ->modifyQueryUsing(function ($query) {
                    return $query->with('category')
                        ->where('is_active', true)
                        ->orderBy('name');
                }),
        ]);
}
```

## Facade Usage

You can also use the Facade for quick access:

```php
use Cooper\FilamentDcatFilters\Facades\FilamentDcatFilters;

FilamentDcatFilters::modalSelectFilter('user_id')
    ->label('User')
    ->model(User::class, 'name', 'id')
    ->dialogTitle('Select User')
```

## API Reference

### Methods

| Method | Description | Parameters |
|--------|-------------|------------|
| `model()` | Set the model class and columns | `$modelClass, $titleColumn = 'name', $keyColumn = 'id'` |
| `relationship()` | Set relationship with optional model class | `$relationship, $titleColumn = 'name', $keyColumn = 'id', $modelClass = null` |
| `multiple()` | Enable multiple selection | `$multiple = true` |
| `dialogTitle()` | Set modal dialog title | `$title` |
| `dialogWidth()` | Set modal dialog width | `$width` (e.g., '900px', '80%') |
| `displayColumns()` | Set columns to display in table | `array $columns` |
| `searchable()` | Set searchable columns | `array $columns` |
| `modifyQueryUsing()` | Modify the base query | `Closure $callback` |

## Comparison with SelectTableFilter

| Feature | SelectTableFilter | ModalSelectFilter |
|---------|------------------|-------------------|
| UI Component | Dropdown Select | Modal Dialog with Table |
| Display | Compact dropdown | Full table with columns |
| Selection UX | Type-ahead search | Visual browsing + search |
| Best For | Simple selections | Complex selections with context |
| Inspiration | Standard Filament | Dcat Admin SelectTable |

## Tips

1. **Use display columns wisely** - Show relevant information but don't overcrowd the table
2. **Enable search** - For large datasets, make key columns searchable
3. **Set appropriate width** - Adjust dialog width based on number of columns
4. **Consider mobile** - The filter is responsive but works best on larger screens
5. **Use relationships** - When filtering by related models, use the `relationship()` method

## UI Behavior

### Selection Summary (Trigger)

- **Single selection**: displays the selected label in the trigger button
- **Multiple selection**: shows the first 2 selected labels as badges, with a `+N` overflow badge when more than 2 items are selected
- Clicking the trigger opens the modal; clicking a badge's `×` removes that item

### Auto-Focus Search

When the modal opens, the search input is automatically focused so users can start typing immediately — especially useful for remote search scenarios with large datasets.

### Error Handling

If label fetching fails (network error, server error), the trigger displays an inline error message with a **Retry** button. The retry button is disabled while a request is in flight, preventing duplicate requests. The selected IDs are preserved — only the display labels fall back to `#id` format.

### Loading Stability

The trigger area uses a fixed minimum height to prevent layout shifts when transitioning between placeholder, loading, and selected states.

### Accessibility

- Trigger button: `aria-haspopup="dialog"`, dynamic `aria-expanded`, `aria-label` with current display text
- Remove badge buttons: `aria-label="Remove {label}"`
- Clear button: `aria-label="Clear selected value"`
- Error alert: `role="alert"` with retry action

### Modal Footer (Responsive)

On narrow screens (< `sm` breakpoint), the modal footer buttons stack vertically (confirm/cancel on top, clear below). On wider screens they display in a single row.

### Selection Preview in Modal

When items are selected, the modal footer shows the count badge plus a preview of the first 1–2 selected names.

## Browser Compatibility

The ModalSelectFilter uses modern JavaScript features and is compatible with:

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Mobile browsers (iOS Safari 14+, Chrome Mobile)

## Search Configuration

Configure search behavior for the modal table:

```php
ModalSelectFilter::make('user_id')
    ->model(User::class, 'name', 'id')
    ->searchable(['name', 'email'])
    ->searchDebounce(300)    // debounce in ms (default: 300)
    ->minSearchLength(2)     // min chars before search fires (default: 1)
```

- `searchDebounce` — delays search requests to reduce server load during rapid typing
- `minSearchLength` — search terms shorter than this show a "please enter at least N characters" hint instead of empty results, preventing broad queries on large tables. The search placeholder also reflects the minimum length requirement.

Defaults can be configured globally in `config/filament-dcat-filters.php` under `remote_search.debounce` and `remote_search.min_length`. Per-filter settings override config defaults.

## Troubleshooting

### Modal doesn't open
- Ensure Livewire is properly installed and configured
- Check browser console for JavaScript errors
- Verify the model class exists and is accessible

### Records not showing
- Check the model query with `modifyQueryUsing()`
- Verify database connectivity
- Check column names in `displayColumns()`

### Search not working
- Ensure columns in `searchable()` exist in the database
- Check for proper database indexes on searchable columns

## Next Steps

- [SelectTable Filter](select-table-filter.md) - The simpler dropdown version
- [Advanced Features](advanced-features.md) - More customization options
- [Usage Examples](usage-example.md) - Real-world examples
