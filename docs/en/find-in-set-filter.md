# FindInSetFilter

Query comma-separated values using MySQL's FIND_IN_SET function.

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\FindInSetFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            FindInSetFilter::make('tags')
                ->options([
                    'php' => 'PHP',
                    'laravel' => 'Laravel',
                    'filament' => 'Filament',
                ]),
        ]);
}
```

## Options

### Static Options

```php
FindInSetFilter::make('categories')
    ->options([
        'tech' => 'Technology',
        'science' => 'Science',
        'art' => 'Art',
    ])
```

### Closure Options

```php
FindInSetFilter::make('tags')
    ->options(fn () => Tag::pluck('name', 'slug')->toArray())
```

## Multiple Selection

Enable selecting multiple values:

```php
FindInSetFilter::make('tags')
    ->options(['php' => 'PHP', 'laravel' => 'Laravel'])
    ->multiple()
```

## Searchable

Make the dropdown searchable for large option lists:

```php
FindInSetFilter::make('tags')
    ->options($manyOptions)
    ->searchable()
```

## Match Logic

### Match Any (OR Logic)

Records matching ANY of the selected values:

```php
FindInSetFilter::make('skills')
    ->options($skills)
    ->multiple()
    ->matchAny()
```

SQL: `FIND_IN_SET('php', skills) OR FIND_IN_SET('laravel', skills)`

### Match All (AND Logic)

Records matching ALL of the selected values:

```php
FindInSetFilter::make('skills')
    ->options($skills)
    ->multiple()
    ->matchAll()
```

SQL: `FIND_IN_SET('php', skills) AND FIND_IN_SET('laravel', skills)`

## Custom Placeholder

```php
FindInSetFilter::make('tags')
    ->options($tags)
    ->placeholder('Filter by tags...')
```

## Complete Example

```php
FindInSetFilter::make('categories')
    ->label('Categories')
    ->options([
        'frontend' => 'Frontend',
        'backend' => 'Backend',
        'devops' => 'DevOps',
        'mobile' => 'Mobile',
    ])
    ->multiple()
    ->searchable()
    ->matchAny()
    ->placeholder('Select categories...')
    ->columnSpan(2),
```

## Use Cases

- Tags stored as comma-separated values
- Legacy database designs without proper many-to-many relationships
- Simple categorization without join tables
- Quick multi-value filtering

## Database Compatibility

This filter uses MySQL's `FIND_IN_SET()` function. For PostgreSQL compatibility, consider using the `JsonFilter` with array columns instead.
