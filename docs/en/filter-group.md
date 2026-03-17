# FilterGroup

Combine multiple filters with AND/OR logic for complex filter conditions.

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\FilterGroup;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            FilterGroup::make('content_search')
                ->orLogic()
                ->filters([
                    LikeFilter::make('title'),
                    LikeFilter::make('description'),
                ]),
        ]);
}
```

## Logic Operators

### OR Logic

Match records where **any** condition is true:

```php
FilterGroup::make('search')
    ->orLogic()
    ->filters([
        LikeFilter::make('title'),
        LikeFilter::make('description'),
        LikeFilter::make('content'),
    ])
```

SQL: `WHERE (title LIKE '%term%' OR description LIKE '%term%' OR content LIKE '%term%')`

### AND Logic (Default)

Match records where **all** conditions are true:

```php
FilterGroup::make('strict_search')
    ->andLogic()
    ->filters([
        LikeFilter::make('title'),
        LikeFilter::make('author'),
    ])
```

SQL: `WHERE title LIKE '%term1%' AND author LIKE '%term2%'`

### Using Logic Method

```php
FilterGroup::make('search')
    ->logic('or')  // or 'and'
    ->filters([...])
```

## Adding Filters

```php
FilterGroup::make('product_search')
    ->filters([
        LikeFilter::make('name'),
        LikeFilter::make('sku'),
        LikeFilter::make('description'),
        LikeFilter::make('brand'),
    ])
```

## Complete Example

### Global Search Box

```php
FilterGroup::make('global_search')
    ->label('Search')
    ->orLogic()
    ->filters([
        LikeFilter::make('title')
            ->placeholder('Title'),
        LikeFilter::make('content')
            ->placeholder('Content'),
        LikeFilter::make('author_name')
            ->placeholder('Author'),
    ])
    ->columnSpan(2),
```

### Multi-Field Exact Match

```php
FilterGroup::make('exact_match')
    ->label('Exact Match')
    ->andLogic()
    ->filters([
        ComparisonFilter::make('category')
            ->eq(),
        ComparisonFilter::make('status')
            ->eq(),
    ]),
```

## Use Cases

- **Global search**: Search across multiple fields with OR logic
- **Complex filtering**: Combine multiple conditions with AND logic
- **Advanced search forms**: Group related filters together
- **Conditional filters**: Apply multiple criteria simultaneously

## Available Methods

| Method | Description |
|--------|-------------|
| `filters(array $filters)` | Set the child filters |
| `logic(string $logic)` | Set logic type ('and' or 'or') |
| `andLogic()` | Use AND logic |
| `orLogic()` | Use OR logic |
| `getLogic()` | Get current logic type |
| `getChildFilters()` | Get array of child filters |

## Notes

- Child filters are displayed together in a single form section
- Each child filter generates its own indicator when active
- The logic affects how the filters are combined in the SQL WHERE clause
- You can nest FilterGroups for more complex logic (though not recommended for usability)

### Multi-Field Filter Support

FilterGroup can handle child filters that use array-based form data (e.g., RangeFilter with `from`/`to` fields). When the child filter's data is an array, it is passed directly to the filter's `apply()` method:

```php
FilterGroup::make('date_and_price')
    ->andLogic()
    ->filters([
        RangeFilter::make('created_at')->date(),
        BetweenFilter::make('price'),
    ])
```

## Filter Compatibility

FilterGroup works with any filter that extends `Filament\Tables\Filters\Filter`:

- LikeFilter
- ComparisonFilter
- BooleanFilter
- EnumFilter
- And more...
