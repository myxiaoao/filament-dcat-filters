# Scope Filter

The Scope Filter provides tab-style quick filtering, inspired by Dcat Admin's scope feature. It's perfect for common filtering scenarios where users need to quickly switch between predefined query conditions.

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\ScopeFilter;

ScopeFilter::make('status')
    ->scopes([
        'all' => ['label' => 'All'],
        'published' => [
            'label' => 'Published',
            'query' => fn($q) => $q->where('status', 'published'),
        ],
        'draft' => [
            'label' => 'Draft',
            'query' => fn($q) => $q->where('status', 'draft'),
        ],
    ])
```

## Configuration Options

### Setting Default Scope

```php
ScopeFilter::make('status')
    ->scopes([
        'all' => [
            'label' => 'All',
            'default' => true, // This scope is selected by default
        ],
        // ... other scopes
    ])
```

### Display Style

Choose between radio buttons (default) or select dropdown:

```php
// Radio buttons (default)
ScopeFilter::make('status')
    ->scopes([...])
    ->style('radio');

// Select dropdown
ScopeFilter::make('status')
    ->scopes([...])
    ->style('select');
```

### Layout Columns

For radio button style, control the number of columns:

```php
ScopeFilter::make('status')
    ->scopes([...])
    ->columns(3); // 3 columns
```

### Badge Display

Show count badges on scope buttons:

```php
ScopeFilter::make('status')
    ->scopes([...])
    ->badge(true); // Enable badges (if implemented)
```

## Scope Configuration

Each scope can have the following properties:

```php
[
    'scope_key' => [
        'label' => 'Display Label',       // Required: shown to users
        'query' => fn($query) => $query,  // Optional: query modification
        'default' => true,                // Optional: default selection
        'badge' => fn($count) => $count,  // Optional: badge count
        'icon' => 'heroicon-o-check',     // Optional: icon (future)
    ]
]
```

## Quick Methods

### Status Scopes

Create common status-based scopes quickly:

```php
ScopeFilter::forStatus('status', [
    'draft' => 'Draft',
    'published' => 'Published',
    'archived' => 'Archived',
])
```

This automatically generates scopes with queries like `where('status', 'draft')`.

### Date Scopes

Create common date-based scopes:

```php
ScopeFilter::forDates('created_at')
```

This generates scopes for:
- All Time (default)
- Today
- This Week
- This Month
- This Year

## Advanced Examples

### Complex Queries

```php
ScopeFilter::make('user_status')
    ->scopes([
        'all' => ['label' => 'All Users'],
        'active' => [
            'label' => 'Active',
            'query' => fn($q) => $q->where('is_active', true)
                ->whereNotNull('last_login_at'),
        ],
        'inactive' => [
            'label' => 'Inactive',
            'query' => fn($q) => $q->where('is_active', false)
                ->orWhereNull('last_login_at'),
        ],
        'premium' => [
            'label' => 'Premium',
            'query' => fn($q) => $q->whereHas('subscription',
                fn($q) => $q->where('plan', 'premium')
            ),
        ],
    ])
    ->columns(4)
```

### Relationship Queries

```php
ScopeFilter::make('has_posts')
    ->scopes([
        'all' => ['label' => 'All'],
        'with_posts' => [
            'label' => 'Has Posts',
            'query' => fn($q) => $q->has('posts'),
        ],
        'without_posts' => [
            'label' => 'No Posts',
            'query' => fn($q) => $q->doesntHave('posts'),
        ],
        'popular' => [
            'label' => 'Popular (10+ posts)',
            'query' => fn($q) => $q->has('posts', '>=', 10),
        ],
    ])
```

### Date Range Scopes

```php
ScopeFilter::make('date_range')
    ->scopes([
        'all' => ['label' => 'All Time'],
        'today' => [
            'label' => 'Today',
            'query' => fn($q) => $q->whereDate('created_at', today()),
        ],
        'yesterday' => [
            'label' => 'Yesterday',
            'query' => fn($q) => $q->whereDate('created_at', today()->subDay()),
        ],
        'last_7_days' => [
            'label' => 'Last 7 Days',
            'query' => fn($q) => $q->whereBetween('created_at', [
                now()->subDays(7),
                now()
            ]),
        ],
        'this_month' => [
            'label' => 'This Month',
            'query' => fn($q) => $q->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year),
        ],
    ])
```

## Tips

1. **Default Scope**: Always include an "All" scope as the default to allow users to reset filtering
2. **Label Clarity**: Use clear, concise labels that users understand immediately
3. **Query Efficiency**: Keep scope queries efficient for better performance
4. **Logical Grouping**: Group related scopes together
5. **Common First**: Place the most commonly used scopes first

## Comparison with Dcat Admin

### Dcat Admin
```php
$filter->scope('published', 'Published')
    ->where('status', 'published');
```

### Filament Dcat Filters
```php
ScopeFilter::make('status')
    ->scopes([
        'published' => [
            'label' => 'Published',
            'query' => fn($q) => $q->where('status', 'published'),
        ],
    ])
```

The Filament version groups all related scopes together and provides more flexibility with closures for complex queries.
