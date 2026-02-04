# Changelog

All notable changes to `filament-dcat-filters` will be documented in this file.

## [Unreleased]

## 1.0.6 - 2026-02-04

### Added

- **Global Table Configuration**: 安装包后自动应用 `FiltersLayout::AboveContent` 和 `FiltersResetActionPosition::Footer`
  - 通过 `Table::configureUsing()` 在 ServiceProvider 中全局设置
  - 配置文件新增 `table` 段，包含 `filters_above_content` 和 `reset_action_in_footer` 开关
  - 用户可在 `config/filament-dcat-filters.php` 中设为 `false` 关闭默认行为
  - `FiltersResetActionPosition` 使用 `enum_exists()` 安全检查，兼容不同 Filament 版本

### Tests

- All **510 tests** passing with **699 assertions**

---

## 1.0.5 - 2026-02-04

### Added

- **Database Driver Adaptation**: PostgreSQL / MySQL / SQLite 自适应 SQL 生成
  - 新增 `HasDatabaseDriver` trait，支持 `driver()` 手动指定、配置文件指定、自动检测三级优先级
  - `LikeFilter`: PostgreSQL 使用原生 `ILIKE` / `NOT ILIKE`
  - `FullTextFilter`: PostgreSQL 使用 `to_tsvector` / `to_tsquery` 全文检索，LIKE 搜索使用 `ILIKE`
  - `RegexFilter`: PostgreSQL 使用 `~` (大小写敏感) / `~*` (大小写不敏感)
  - `FindInSetFilter`: PostgreSQL 使用 `ANY(string_to_array())` 替代 `FIND_IN_SET()`
  - 配置文件新增 `database` 段 (`driver`, `case_insensitive`)

- **Relationship Query Support**: LikeFilter、InFilter 支持关系查询
  - 新增 `HasRelationship` trait，提供 `relationship()` 方法
  - `LikeFilter`: 通过 `whereHas` 在关联模型上执行 LIKE/ILIKE 搜索
  - `InFilter`: 通过 `whereHas` 在关联模型上执行 IN/NOT IN 查询

- **ComparisonFilter Money Conversion**: 分/元自动换算
  - `money(int $divideBy = 100)`: 用户输入元，查询时自动乘以倍数转为分
  - `moneySuffix(string $suffix)`: 输入框显示单位后缀

- **EnumFilter column() Method**: 补齐 `column()` 方法，与其他 Filter 保持一致

- **FindInSetFilter column() Method**: 补齐 `column()` 方法

### Changed

- `phpunit.xml.dist` 新增 Feature testsuite

### Tests

- 新增 `HasDatabaseDriverTest` 和 `HasRelationshipTest` 测试文件
- 已有 Filter 测试追加 Relationship Support、Database Driver、Column Name、Money Support 等 describe 块
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
