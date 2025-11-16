# SelectTable Filter

The SelectTable Filter provides a modal table selector for choosing related records. It displays a full Filament table with search, sort, and pagination features, making it perfect for selecting from large datasets.

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\SelectTableFilter;

SelectTableFilter::make('author_id')
    ->label('Author')
    ->model(\App\Models\User::class)
    ->tableColumns([
        Tables\Columns\TextColumn::make('name'),
        Tables\Columns\TextColumn::make('email'),
    ])
    ->searchable(['name', 'email']);
```

## Configuration Options

### Set Model Class

```php
SelectTableFilter::make('author_id')
    ->model(\App\Models\User::class);
```

### Set Relationship

```php
SelectTableFilter::make('author')
    ->relationship('author', 'name') // relationship name, title column
    ->tableColumns([...]);
```

### Table Columns

Define which columns to show in the selection table:

```php
SelectTableFilter::make('category_id')
    ->model(Category::class)
    ->tableColumns([
        Tables\Columns\TextColumn::make('name')
            ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('slug'),
        Tables\Columns\TextColumn::make('posts_count')
            ->counts('posts'),
    ]);
```

### Searchable Columns

Enable search on specific columns:

```php
SelectTableFilter::make('user_id')
    ->model(User::class)
    ->searchable(['name', 'email', 'username']);

// Or simply enable search (searches 'name' by default)
SelectTableFilter::make('user_id')
    ->model(User::class)
    ->searchable();

// Disable search
SelectTableFilter::make('user_id')
    ->model(User::class)
    ->searchable(false);
```

### Multiple Selection

Allow selecting multiple records:

```php
SelectTableFilter::make('categories')
    ->model(Category::class)
    ->multiple()
    ->tableColumns([...]);
```

### Modal Width

Customize the modal size:

```php
SelectTableFilter::make('author_id')
    ->model(User::class)
    ->modalWidth('5xl') // xs, sm, md, lg, xl, 2xl, 3xl, 4xl, 5xl, 6xl, 7xl
    ->tableColumns([...]);
```

### Modify Query

Apply custom query modifications:

```php
SelectTableFilter::make('active_users')
    ->model(User::class)
    ->modifyQueryUsing(fn($query) =>
        $query->where('is_active', true)
            ->whereNotNull('email_verified_at')
    )
    ->tableColumns([...]);
```

## Complete Examples

### User Selection with Search

```php
SelectTableFilter::make('author_id')
    ->label('Author')
    ->model(\App\Models\User::class)
    ->tableColumns([
        Tables\Columns\TextColumn::make('name')
            ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('email')
            ->searchable(),
        Tables\Columns\TextColumn::make('posts_count')
            ->counts('posts')
            ->label('Posts'),
        Tables\Columns\TextColumn::make('created_at')
            ->date()
            ->sortable(),
    ])
    ->searchable(['name', 'email'])
    ->modalWidth('4xl');
```

### Multiple Category Selection

```php
SelectTableFilter::make('categories')
    ->label('Categories')
    ->model(\App\Models\Category::class)
    ->multiple()
    ->tableColumns([
        Tables\Columns\TextColumn::make('name')
            ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('slug'),
        Tables\Columns\ColorColumn::make('color'),
        Tables\Columns\TextColumn::make('products_count')
            ->counts('products'),
    ])
    ->searchable(['name', 'slug'])
    ->modalWidth('3xl');
```

### Tag Selection with Colors

```php
SelectTableFilter::make('tags')
    ->label('Tags')
    ->model(\App\Models\Tag::class)
    ->multiple()
    ->tableColumns([
        Tables\Columns\TextColumn::make('name')
            ->badge()
            ->color(fn($record) => $record->color),
        Tables\Columns\TextColumn::make('slug'),
        Tables\Columns\TextColumn::make('usage_count')
            ->label('Used')
            ->sortable(),
    ])
    ->searchable(['name'])
    ->modalWidth('2xl');
```

### Active Users Only

```php
SelectTableFilter::make('assignee_id')
    ->label('Assignee')
    ->model(\App\Models\User::class)
    ->modifyQueryUsing(fn($query) =>
        $query->where('is_active', true)
            ->whereHas('roles', fn($q) =>
                $q->whereIn('name', ['admin', 'manager'])
            )
    )
    ->tableColumns([
        Tables\Columns\TextColumn::make('name')
            ->searchable(),
        Tables\Columns\TextColumn::make('department.name')
            ->label('Department'),
        Tables\Columns\TextColumn::make('role.name')
            ->label('Role'),
    ])
    ->searchable(['name', 'email']);
```

## Using with Relationships

When filtering by relationships, the filter automatically handles `whereHas` queries:

```php
// Single selection
SelectTableFilter::make('author')
    ->relationship('author', 'name')
    ->tableColumns([...]);

// Generates: $query->whereHas('author', fn($q) => $q->where('id', $selectedId))

// Multiple selection
SelectTableFilter::make('categories')
    ->relationship('categories', 'name')
    ->multiple()
    ->tableColumns([...]);

// Generates: $query->whereHas('categories', fn($q) => $q->whereIn('id', $selectedIds))
```

## Configuration File

Default settings in `config/filament-dcat-filters.php`:

```php
'select_table' => [
    'modal_width' => '3xl',
    'per_page' => 10,
    'searchable' => true,
    'multiple' => false,
],
```

## User Interaction Flow

### Single Selection
1. User clicks the filter field
2. Modal opens with a full table
3. User can search, sort, and paginate
4. User clicks a row to select
5. Modal closes automatically
6. Filter is applied

### Multiple Selection
1. User clicks the filter field
2. Modal opens with a full table
3. User can search, sort, and paginate
4. User selects multiple rows via checkboxes
5. User clicks "Select (X)" button
6. Modal closes
7. Filter is applied

## Advanced Features

### Customizing Display

The selected value(s) are displayed using the `titleColumn`:

```php
SelectTableFilter::make('category_id')
    ->relationship('category', 'name') // 'name' is the title column
```

You can also customize this in your model:

```php
class Category extends Model
{
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->products_count} products)";
    }
}

// Then use:
SelectTableFilter::make('category_id')
    ->relationship('category', 'display_name');
```

## Tips

1. **Performance**: For large datasets, ensure proper database indexing on searchable columns
2. **UX**: Keep the number of columns reasonable (3-5) for better modal display
3. **Search**: Always make key columns searchable for better user experience
4. **Counts**: Show relationship counts to help users make informed selections
5. **Width**: Use appropriate modal widths based on column count and content

## Comparison with Dcat Admin

### Dcat Admin
```php
$filter->equal('user_id')
    ->selectTable(UserTable::make())
    ->title('Select User')
    ->model(User::class, 'id', 'name');
```

### Filament Dcat Filters
```php
SelectTableFilter::make('user_id')
    ->label('User')
    ->model(User::class)
    ->tableColumns([
        Tables\Columns\TextColumn::make('name'),
        Tables\Columns\TextColumn::make('email'),
    ])
    ->searchable(['name', 'email']);
```

The Filament version provides:
- Full Filament Table Builder integration
- Native Filament UI components
- Better type safety with column definitions
- More flexible configuration options

## Limitations

1. **Livewire**: Requires Livewire 3.0+
2. **Modal**: Uses Filament's modal system
3. **Performance**: May be slower with very large tables (100,000+ records)
4. **Customization**: For highly custom selection UIs, consider building a custom filter

## Future Enhancements

Planned features for future versions:
- Preset filters within the modal
- Quick search input at the top
- Recent selections for faster re-selection
- Bulk actions within selection modal
- Custom row actions
