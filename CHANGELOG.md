# Changelog

All notable changes to `filament-dcat-filters` will be documented in this file.

## v1.0.8 - 2026-02-05

### Added

- **RangeFilter datetime() end time default**: Time picker defaults to 23:59:59 for the "to" field
  - When opening the datetime picker, time defaults to 23:59:59 (or 23:59 for formats without seconds)
  - Frontend value is passed directly to backend without modification
  

## [Unreleased]

## 1.0.7 - 2026-02-04

### Added

- **Global Table Configuration**: Auto-apply `FiltersLayout::AboveContent`, `FiltersResetActionPosition::Footer`, and `filtersFormColumns(4)` on package install
  
  - Applied globally via `Table::configureUsing()` in ServiceProvider
  - New `table` config section with `filters_above_content`, `reset_action_in_footer`, and `filters_form_columns` options
  - Users can set any option to `false` / `null` in `config/filament-dcat-filters.php` to disable
  - `FiltersResetActionPosition` uses `enum_exists()` guard for Filament version compatibility
  
- **Database Driver Adaptation**: Adaptive SQL generation for PostgreSQL / MySQL / SQLite
  
  - New `HasDatabaseDriver` trait with three-level priority: filter-level override > config > auto-detect from connection
  - `LikeFilter`: PostgreSQL uses native `ILIKE` / `NOT ILIKE`
  - `FullTextFilter`: PostgreSQL uses `to_tsvector` / `to_tsquery` for full-text search, `ILIKE` for LIKE-based search
  - `RegexFilter`: PostgreSQL uses `~` (case-sensitive) / `~*` (case-insensitive)
  - `FindInSetFilter`: PostgreSQL uses `ANY(string_to_array())` instead of `FIND_IN_SET()`
  - New `database` config section (`driver`, `case_insensitive`)
  
- **Relationship Query Support**: LikeFilter and InFilter now support relationship queries
  
  - New `HasRelationship` trait with `relationship()` method
  - `LikeFilter`: Performs LIKE/ILIKE search on related models via `whereHas`
  - `InFilter`: Performs IN/NOT IN queries on related models via `whereHas`
  
- **ComparisonFilter Money Conversion**: Automatic cent/unit conversion
  
  - `money(int $divideBy = 100)`: User enters display units, query multiplies to storage units
  - `moneySuffix(string $suffix)`: Display a unit suffix on the input field
  
- **EnumFilter column() Method**: Added `column()` method for consistency with other filters
  
- **FindInSetFilter column() Method**: Added `column()` method for custom column name mapping
  

### Changed

- `phpunit.xml.dist` now includes Feature testsuite

### Tests

- Added `HasDatabaseDriverTest` and `HasRelationshipTest` test files
- Appended Relationship Support, Database Driver, Column Name, and Money Support describe blocks to existing filter tests
- Total: **510 tests** with **699 assertions** (all passing)


---

## 1.0.3 - 2026-01-21

### Added

- **Filament v5 Support**: Added support for Filament v5 alongside v4
  - Updated composer.json to allow `filament/filament: ^4.0 || ^5.0`
  - Filament v5 primarily adds Livewire v4 support with no API changes
  - All existing code remains compatible without modifications
  

### Documentation

- Updated README.md and README_CN.md with Filament v5 compatibility
- Updated version compatibility tables to include Filament 5.x
- Updated badges to show "4.x | 5.x" support

### Tests

- All **459 tests** passing with **623 assertions**


---

## 1.0.2 - 2025-01-11

### Added

- **column() Method**: Added `column()` method to all applicable filters for custom column name mapping
  - Supported filters: LikeFilter, InFilter, ComparisonFilter, RangeFilter, DateComponentFilter, RelativeDateFilter, JsonFilter, HiddenFilter, SelectTableFilter, ModalSelectFilter, RegexFilter
  - Allows filter name to differ from database column name
  - Useful for multiple filters on the same column with different configurations
  

### Tests

- Added comprehensive tests for `column()` method across all supported filters
- Total: **461 tests** with **630 assertions** (all passing)

### Documentation

- Updated feature-analysis.md to reflect all implemented features
- Updated future-improvements.md with implementation status
- Added `column()` method documentation to quick-filters.md
- Updated package-structure.md with current file structure
- Updated documentation-structure.md with complete file listing


---

## 1.0.1 - 2025-01-10

### Fixed

- Fixed ModalSelectFilter hidden input binding to Livewire state
- Improved ModalSelectFilter UI with row click selection
- Added label display to ModalSelectFilter view
- Changed date display format to Y-m-d in DatePicker
- Added displayFormat to DatePicker to hide time in date-only filters


---

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
