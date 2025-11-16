# Filament Dcat Filters

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cooper/filament-dcat-filters.svg?style=flat-square)](https://packagist.org/packages/cooper/filament-dcat-filters)
[![Total Downloads](https://img.shields.io/packagist/dt/cooper/filament-dcat-filters.svg?style=flat-square)](https://packagist.org/packages/cooper/filament-dcat-filters)

Bring [Dcat Admin](https://github.com/jqhph/dcat-admin)'s powerful filter features to [Filament](https://filamentphp.com). This package provides a collection of enhanced filters that make building admin panels faster and more intuitive.

English Documentation | [中文文档](README_CN.md)

## Features

### Core Filters
- 🎯 **Scope Filter** - Tab-style quick filters for common queries
- 📊 **Range Filter** - Simplified date/number range filtering (3 lines of code!)
- 📅 **Date Component Filter** - Filter by year, month, or day separately
- 🔍 **SelectTable Filter** - Modal table selector with search and pagination
- 🎭 **Modal Select Filter** - Dcat Admin style modal with full table display
- 🔢 **Between Filter** - Numeric range filtering shortcut
- 🙈 **Hidden Filter** - URL parameter-based filtering without UI

### Quick Filters
- ⚡ **LIKE Filter** - Text search with wildcard control (supports NOT LIKE)
- 📋 **IN Filter** - Multiple value selection (supports NOT IN)
- 🔢 **Comparison Filter** - Comparison operators (>, <, >=, <=, =, !=)

### Additional Features
- 🎨 **Highly Customizable** - Extensive customization options for each filter
- 📱 **Mobile Friendly** - Responsive design for all screen sizes
- 🌐 **Bilingual Docs** - Complete English and Chinese documentation

## Version Compatibility

| Filament | Filament Dcat Filters | PHP    | Laravel |
|----------|----------------------|--------|---------|
| 4.x      | 1.x                  | ^8.2   | ^12.0   |

## Installation

You can install the package via composer:

```bash
composer require cooper/filament-dcat-filters
```

Optionally, you can publish the config file:

```bash
php artisan vendor:publish --tag="filament-dcat-filters-config"
```

Optionally, you can publish the views:

```bash
php artisan vendor:publish --tag="filament-dcat-filters-views"
```

## Quick Start

### Scope Filter

Perfect for quick filtering with tab-style buttons:

```php
use Cooper\FilamentDcatFilters\Filters\ScopeFilter;

ScopeFilter::make('status')
    ->scopes([
        'all' => 'All',
        'active' => 'Active',
        'inactive' => 'Inactive',
    ])
```

**[View detailed documentation →](docs/en/scope-filter.md)**

### Range Filter

Simplified date/number range filtering:

```php
use Cooper\FilamentDcatFilters\Filters\RangeFilter;

RangeFilter::make('created_at')->datetime()
```

**[View detailed documentation →](docs/en/range-filter.md)**

### SelectTable Filter

Modal table selector with search and pagination:

```php
use Cooper\FilamentDcatFilters\Filters\SelectTableFilter;

SelectTableFilter::make('user_id')
    ->relationship('user', 'name')
    ->multiple()
```

**[View detailed documentation →](docs/en/select-table-filter.md)**

### Date Component Filter

Filter by year, month, or day components:

```php
use Cooper\FilamentDcatFilters\Filters\DateComponentFilter;

DateComponentFilter::make('created_at')->year()
DateComponentFilter::make('birth_date')->month()
DateComponentFilter::make('published_at')->day()
```

**[View detailed documentation →](docs/en/date-component-filter.md)**

### Modal Select Filter

Dcat Admin style modal with full table display:

```php
use Cooper\FilamentDcatFilters\Filters\ModalSelectFilter;

ModalSelectFilter::make('user_id')
    ->model(User::class, 'name', 'id')
    ->dialogTitle('Select User')
    ->displayColumns(['id' => 'ID', 'name' => 'Name', 'email' => 'Email'])
    ->searchable(['name', 'email'])
    ->multiple()
```

**[View detailed documentation →](docs/en/modal-select-filter.md)**

### Quick Filters

Built-in filters for common operations:

```php
use Cooper\FilamentDcatFilters\Filters\{LikeFilter, InFilter, ComparisonFilter, BetweenFilter};

// LIKE search (with NOT LIKE support)
LikeFilter::make('title'),
LikeFilter::make('spam_keywords')->notLike(), // Exclude matches

// IN array (with NOT IN support)
InFilter::make('category_id')
    ->options(Category::pluck('name', 'id')->toArray()),
InFilter::make('blocked_users')->notIn(), // Exclude selected

// Comparison (>, <, =, >=, <=, !=)
ComparisonFilter::make('views')->gte()->label('Minimum Views'),

// Between (numeric range)
BetweenFilter::make('price')->label('Price Range'),
```

**[View detailed documentation →](docs/en/quick-filters.md)**

### Hidden Filter

URL parameter-based filtering (no UI):

```php
use Cooper\FilamentDcatFilters\Filters\HiddenFilter;

// Pre-filter by tenant
HiddenFilter::make('tenant_id')
    ->default(auth()->user()->tenant_id)
    ->eq()
```

**[View detailed documentation →](docs/en/advanced-features.md#hiddenfilter-usage-guide)**

## Documentation

### Core Filters
- 📖 [Scope Filter](docs/en/scope-filter.md) - Tab-style quick filters
- 📖 [Range Filter](docs/en/range-filter.md) - Date/number range filtering
- 📖 [Date Component Filter](docs/en/date-component-filter.md) - Year/Month/Day filtering
- 📖 [SelectTable Filter](docs/en/select-table-filter.md) - Modal table selector
- 📖 [Modal Select Filter](docs/en/modal-select-filter.md) - Dcat Admin style modal table selector
- 📖 [Quick Filters](docs/en/quick-filters.md) - LIKE, IN, GT, LT, BETWEEN filters

### Guides & References
- 📖 [Usage Examples](docs/en/usage-example.md) - Complete working examples
- 📖 [Demo Guide](docs/en/demo-guide.md) - Interactive demonstrations
- 📖 [Advanced Features](docs/en/advanced-features.md) - API support, InputMask, FindInSet, Hidden filters
- 📖 [Comparison with Dcat Admin](docs/en/comparison.md) - Feature comparison
- 📖 [Package Structure](docs/en/package-structure.md) - Package architecture
- 📖 [Documentation Structure](docs/en/documentation-structure.md) - Documentation organization

## Facade Usage

You can also use the Facade for quick access:

```php
use Cooper\FilamentDcatFilters\Facades\FilamentDcatFilters;

FilamentDcatFilters::scopeFilter('status')->scopes([...]);
FilamentDcatFilters::rangeFilter('created_at')->datetime();
```

## Testing

```bash
composer test
```

## Code Quality

```bash
# Format code
composer format

# Static analysis
composer analyse
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

If you discover any security-related issues, please email `myxiaoao@gmail.com`.

## Credits

- [Cooper](https://github.com/myxiaoao)
- Inspired by [Dcat Admin](https://github.com/jqhph/dcat-admin)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
