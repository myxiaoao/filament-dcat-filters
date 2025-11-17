# Changelog

All notable changes to `filament-dcat-filters` will be documented in this file.

## [Unreleased]

## 1.0.0 - 2025-11-17

### Added

#### Core Filters
- **ScopeFilter** - Tab-style quick filtering with customizable scopes
- **RangeFilter** - Simplified date/number range filtering (3 lines of code!)
  - Support for date, datetime, and integer ranges
  - Customizable input types and formats
- **DateComponentFilter** - Filter by year, month, or day components separately
  - `year()`, `month()`, `day()` methods for granular date filtering
- **SelectTableFilter** - Modal table selector with search and pagination
  - Relationship support with `relationship()` method
  - Multiple selection support
  - Customizable columns and search
- **ModalSelectFilter** - Dcat Admin style modal with full table display
  - Display multiple columns in modal
  - Searchable with custom search columns
  - Single and multiple selection modes
- **HiddenFilter** - URL parameter-based filtering without UI
  - Perfect for pre-filtering by tenant, user, or context
  - Supports all comparison operators

#### Quick Filters
- **LikeFilter** - Text search with wildcard control
  - NOT LIKE support via `notLike()` method
  - Wildcard position control: `both`, `startsWith()`, `endsWith()`, `exact()`
  - Case sensitivity options
- **InFilter** - Multiple value selection
  - NOT IN support via `notIn()` method
  - Searchable dropdown
  - Multiple selection with checkboxes
- **ComparisonFilter** - Comparison operators (>, <, >=, <=, =, !=)
  - All standard comparison operations: `gt()`, `gte()`, `lt()`, `lte()`, `eq()`, `ne()`
  - Numeric and integer input types
- **BetweenFilter** - Numeric range filtering shortcut
  - Alias for `RangeFilter::make()->integer()`

#### Features
- Comprehensive bilingual documentation (English and Chinese)
- Fully customizable filter options
- Mobile-friendly responsive design
- Integration with Filament v4
- Support for API data sources
- InputMask integration for formatted inputs
- FindInSet filter support
- Facade for quick access to filters

#### Development
- PHP 8.3 requirement
- Pest v4 testing framework
- PHPUnit v12
- Architecture tests for code quality
- Laravel Pint code formatting
- PHPStan static analysis
- Complete test infrastructure

### Requirements
- PHP ^8.3
- Laravel ^12.0
- Filament ^4.0
