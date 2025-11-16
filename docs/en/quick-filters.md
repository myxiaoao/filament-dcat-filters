# Quick Filters

Quick Filters provide simple, single-line APIs for common filtering operations. These filters are inspired by Dcat Admin's quick filter methods.

## LikeFilter

Search for text using LIKE queries with automatic wildcard handling.

### Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\LikeFilter;

LikeFilter::make('title')
    ->label('Title');
```

### Configuration Options

#### Wildcard Position

```php
// Both sides (default): %value%
LikeFilter::make('title')->wildcards('both');

// Starts with: value%
LikeFilter::make('title')->startsWith();

// Ends with: %value
LikeFilter::make('title')->endsWith();

// Exact match: value
LikeFilter::make('title')->exact();
```

#### Case Sensitivity

```php
// Case insensitive (default)
LikeFilter::make('title')->insensitive();

// Case sensitive
LikeFilter::make('title')->sensitive();
```

#### Custom Operator

```php
// Use ILIKE (PostgreSQL)
LikeFilter::make('title')->operator('ilike');
```

#### Negation (NOT LIKE)

Exclude records that match the pattern:

```php
// NOT LIKE - exclude matching records
LikeFilter::make('title')
    ->label('Exclude Title')
    ->notLike();

// Alternative: Use negate() method
LikeFilter::make('title')
    ->label('Exclude Title')
    ->negate();
```

**Examples:**

```php
// Exclude spam emails
LikeFilter::make('email')
    ->label('Exclude Email Domain')
    ->endsWith()
    ->notLike(),  // Excludes emails ending with the input

// Exclude products with specific keywords
LikeFilter::make('name')
    ->label('Exclude Product Name')
    ->notLike()
    ->insensitive(),

// Exclude titles starting with certain text
LikeFilter::make('title')
    ->label('Exclude Title Prefix')
    ->startsWith()
    ->negate(),
```

### Examples

```php
// Search in multiple columns (create multiple filters)
LikeFilter::make('title')->label('Title'),
LikeFilter::make('description')->label('Description'),

// Email search
LikeFilter::make('email')
    ->label('Email')
    ->insensitive(),

// Code search (exact)
LikeFilter::make('sku')
    ->label('SKU')
    ->exact()
    ->sensitive(),
```

---

## InFilter

Filter by selecting one or multiple values from a list.

### Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\InFilter;

InFilter::make('status')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ]);
```

### Configuration Options

#### Multiple Selection

```php
InFilter::make('category_id')
    ->options(Category::pluck('name', 'id'))
    ->multiple(); // Use checkbox list
```

#### Searchable

```php
InFilter::make('tag_id')
    ->options(Tag::pluck('name', 'id'))
    ->searchable(); // Add search to dropdown
```

#### Negation (NOT IN)

Exclude records with specific values:

```php
// NOT IN - exclude selected values
InFilter::make('status')
    ->label('Exclude Status')
    ->options([
        'draft' => 'Draft',
        'archived' => 'Archived',
    ])
    ->notIn();

// Alternative: Use negate() method
InFilter::make('status')
    ->label('Exclude Status')
    ->options([...])
    ->negate();

// Multiple exclusion with search
InFilter::make('category_id')
    ->label('Exclude Categories')
    ->options(Category::pluck('name', 'id'))
    ->multiple()
    ->searchable()
    ->notIn();
```

**Use Cases:**

```php
// Exclude specific user roles
InFilter::make('role')
    ->label('Exclude Roles')
    ->options([
        'guest' => 'Guest',
        'banned' => 'Banned',
    ])
    ->multiple()
    ->notIn(),

// Exclude products from certain categories
InFilter::make('category_id')
    ->label('Exclude Categories')
    ->options(Category::pluck('name', 'id'))
    ->multiple()
    ->searchable()
    ->notIn(),

// Exclude specific tags
InFilter::make('tag_ids')
    ->label('Exclude Tags')
    ->options(Tag::pluck('name', 'id'))
    ->multiple()
    ->notIn(),
```

### Examples

```php
// Single selection
InFilter::make('status')
    ->label('Status')
    ->options([
        'active' => 'Active',
        'inactive' => 'Inactive',
    ]),

// Multiple selection with search
InFilter::make('categories')
    ->label('Categories')
    ->options(Category::pluck('name', 'id'))
    ->multiple()
    ->searchable(),

// Tags with checkbox list
InFilter::make('tags')
    ->label('Tags')
    ->options(Tag::pluck('name', 'id'))
    ->multiple(),
```

---

## ComparisonFilter

Filter using comparison operators (>, <, >=, <=, =, !=).

### Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;

ComparisonFilter::make('price')
    ->gt() // Greater than
    ->label('Minimum Price');
```

### Operators

```php
// Greater than (>)
ComparisonFilter::make('views')->gt();

// Greater than or equal (>=)
ComparisonFilter::make('views')->gte();

// Less than (<)
ComparisonFilter::make('views')->lt();

// Less than or equal (<=)
ComparisonFilter::make('views')->lte();

// Equal (=)
ComparisonFilter::make('views')->eq();

// Not equal (!=)
ComparisonFilter::make('views')->ne();
```

### Input Types

```php
// Numeric (allows decimals)
ComparisonFilter::make('price')
    ->gt()
    ->numeric();

// Integer only
ComparisonFilter::make('quantity')
    ->gte()
    ->integer();
```

### Examples

```php
// Minimum price
ComparisonFilter::make('price')
    ->label('Minimum Price')
    ->gte()
    ->numeric(),

// Maximum age
ComparisonFilter::make('age')
    ->label('Maximum Age')
    ->lte()
    ->integer(),

// Exact quantity
ComparisonFilter::make('stock')
    ->label('Stock Equals')
    ->eq()
    ->integer(),

// Exclude value
ComparisonFilter::make('status_id')
    ->label('Exclude Status')
    ->ne(),
```

---

## BetweenFilter

Simplified numeric range filtering (alias for `RangeFilter` with integer type).

### Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\BetweenFilter;

BetweenFilter::make('price')
    ->label('Price Range');
```

### Examples

```php
// Price range
BetweenFilter::make('price')
    ->label('Price')
    ->numeric(),

// Age range
BetweenFilter::make('age')
    ->label('Age Range'),

// Quantity range
BetweenFilter::make('stock')
    ->label('Stock Level'),
```

**Note**: `BetweenFilter` is essentially a shortcut for:
```php
RangeFilter::make('column')->integer()
```

---

## Common Patterns

### E-commerce Product Filtering

```php
use Cooper\FilamentDcatFilters\Filters\{LikeFilter, InFilter, ComparisonFilter, BetweenFilter};

// Search by name
LikeFilter::make('name')
    ->label('Product Name'),

// Category
InFilter::make('category_id')
    ->label('Category')
    ->options(Category::pluck('name', 'id'))
    ->multiple(),

// Price range
BetweenFilter::make('price')
    ->label('Price Range')
    ->numeric(),

// Minimum rating
ComparisonFilter::make('rating')
    ->label('Minimum Rating')
    ->gte()
    ->numeric(),

// Stock status
InFilter::make('stock_status')
    ->options([
        'in_stock' => 'In Stock',
        'out_of_stock' => 'Out of Stock',
        'preorder' => 'Pre-order',
    ]),
```

### User Management

```php
// Search users
LikeFilter::make('name')
    ->label('Name or Email'),

// Role filter
InFilter::make('role')
    ->options([
        'admin' => 'Administrator',
        'editor' => 'Editor',
        'viewer' => 'Viewer',
    ])
    ->multiple(),

// Registration date (use RangeFilter instead)
RangeFilter::make('created_at')
    ->label('Registration Date')
    ->date(),

// Minimum posts
ComparisonFilter::make('posts_count')
    ->label('Minimum Posts')
    ->gte()
    ->integer(),
```

### Blog Post Filtering

```php
// Search title/content
LikeFilter::make('title')
    ->label('Title'),

// Status
InFilter::make('status')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
        'scheduled' => 'Scheduled',
    ]),

// Minimum views
ComparisonFilter::make('views')
    ->label('Minimum Views')
    ->gte()
    ->integer(),

// Word count range
BetweenFilter::make('word_count')
    ->label('Word Count'),
```

## Configuration

Default settings in `config/filament-dcat-filters.php`:

```php
'quick_filters' => [
    'like_operator' => 'like', // 'like' or 'ilike'
    'case_sensitive' => false,
    'like_wildcards' => 'both', // 'both', 'start', 'end', 'none'
],
```

## Tips

1. **LikeFilter**: Perfect for text search fields
2. **InFilter**: Best for categorical data with predefined options
3. **ComparisonFilter**: Great for numeric comparisons
4. **BetweenFilter**: Quick shortcut for numeric ranges
5. **Combine**: Use multiple quick filters together for powerful filtering

## Comparison with Dcat Admin

### Dcat Admin
```php
$filter->like('title');
$filter->in('status')->multipleSelect([...]);
$filter->gt('views');
$filter->between('price');
```

### Filament Dcat Filters
```php
LikeFilter::make('title');
InFilter::make('status')->options([...])->multiple();
ComparisonFilter::make('views')->gt();
BetweenFilter::make('price');
```

The API is very similar, making migration from Dcat Admin straightforward!
