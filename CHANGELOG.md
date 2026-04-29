# Changelog

All notable changes to `filament-dcat-filters` will be documented in this file.

## v2.1.1 - 2026-04-26

### Changed

- **RangeFilter / BetweenFilter 默认 `columnSpan` 由 1 改为 2** — 在窄栅格 toolbar（例如 `filtersFormColumns(3)`）下，inline label + from/to 三段塞进单格会过度拥挤；默认占两格让输入框拥有合理宽度。如需保留旧行为可显式 `->columnSpan(1)` 覆盖。
- 受影响：`RangeFilter` 的 `date()` / `datetime()` / `time()` / `numeric()` / `integer()` 五种类型，以及 `BetweenFilter` 全部模式。

### Tests

- 同步更新 `RangeFilterTest` / `BetweenFilterTest` 的 `default column span` 断言。
- 总计：**1115 tests / 1680 assertions**，全部通过。

## v2.1.0 - 2026-04-07

### Fixed — 4 Functional Defects

- **[High] LocalStorage restore broken** — `restoreFiltersFromLocalStorage()` was missing Livewire `#[On]` attribute, so dispatched events never reached the PHP method. Added `#[On('restoreFiltersFromLocalStorage')]` to complete the event chain.
- **[High] ModalSelectFilter initial state not synced** — when filters were restored via URL, session, or default state, the trigger UI showed empty while the query was active. Alpine `init()` now reads the hidden input value and fetches labels; the child Livewire component receives `$getState()` instead of a hardcoded empty array.
- **[Medium] Reset cleared all resources' LocalStorage** — `ResetFiltersAction` dispatched a generic event without a key, causing all `filament-dcat-filters:*` localStorage entries to be deleted. Now passes the component-specific key; JS clears only that key.
- **[Medium] FullTextFilter SQL error with relation columns in fulltext mode** — `searchIn(['title', 'department.name'])->fullText()` passed dot-path columns directly into `MATCH()` / `to_tsvector()`. New `applyFullTextWithRelations()` separates local columns (fulltext) from relation columns (LIKE + `whereHas`).

### Added — ModalSelectFilter UX Enhancements

- **Selection summary** — multi-select trigger shows first 2 labels as badges with `+N` overflow count; single-select shows label with full-text tooltip on truncation
- **Auto-confirm on select** — new `autoConfirmOnSelect()` method: in single-select mode, selecting a row immediately confirms and closes the modal (no effect in multi-select)
- **Error state with retry** — `fetchLabels` failures show inline error message with a retry button (disabled during loading to prevent duplicate requests)
- **Loading stability** — trigger area uses fixed `min-h-[2rem]` to prevent layout shifts between placeholder / loading / selected states
- **Auto-focus search** — modal search input is automatically focused on open via `requestAnimationFrame`
- **Search too short hint** — when `minSearchLength > 1`, empty state shows "Please enter at least N characters" instead of generic "No records found"; search placeholder also reflects the minimum
- **Clear notice** — clearing selection shows a 2-second transient "Selection cleared" message (`role="status"`)
- **Responsive modal footer** — buttons stack vertically on mobile (`flex-col-reverse`), single row on `sm:+`
- **Selection preview in modal** — footer shows count badge plus first 1–2 selected names with full-list tooltip
- **Overflow tooltip** — `+N` badge and truncated labels show complete text on hover

### Added — Accessibility

- Trigger button: `title` with localized "Open {label} selection dialog"
- Remove badge buttons: dynamic `aria-label="Remove {label}"` and `title`
- Clear button: `aria-label="Clear selected value"` and `title`
- Error alert: `role="alert"` with retry action
- 6 new accessibility translation keys across en / zh_CN / zh_TW

### Added — Tooling

- **PHPStan level 5** — new `phpstan.neon.dist` with larastan + deprecation-rules; baseline locks 200 framework-compatibility errors; 58 fixable errors resolved (`nullCoalesce.expr`, `function.alreadyNarrowedType`, `argument.type`, etc.)
- **`composer analyse` fixed** — script now works out of the box (previously failed with no analysis paths)

### Changed

- `PersistsFiltersInLocalStorage::getLocalStorageKey()` visibility changed from `protected` to `public` (needed by `ResetFiltersAction`)
- `ModalSelectTable::searchDebounce()` now passes `(string)` cast to satisfy Filament's type signature
- `ModalSelectTable::emptyStateHeading()` and `emptyStateDescription()` are now dynamic closures that detect search-too-short state

### Documentation

- ModalSelectFilter docs (en + zh_CN) — new "UI Behavior" section covering all UX enhancements
- CLAUDE.md — added ModalSelect protocol boundaries, debugging guide entries, verification checklist items
- `.claude/rules/php-conventions.md` — added Blade view conventions

### Tests

- Total: **1115 tests** with **1680 assertions** (all passing)
- New: ModalSelectTable Livewire integration tests (26 tests) — mount, single/multi select, toggle, auto-confirm, confirm/cancel/clear, query builder, full lifecycle, table configuration
- New: FullTextFilter relation column separation tests (2 tests)
- New: LocalStorage `#[On]` attribute verification test
- New: ResetFiltersAction `resolveLocalStorageKey` tests (2 tests)
- New: ModalSelectFilter `autoConfirmOnSelect` tests (4 tests)
- New: Accessibility translation tests for zh_TW + 6 new keys across en/zh_CN/zh_TW

## v2.0.0 - 2026-04-04

### Breaking Changes

- **FilterGroup state structure** — child filter form data is now namespaced under each filter's name (e.g., `data[title][value]` instead of `data[value]`). This fixes field name collisions when multiple filters of the same type are used together. **If you have custom code that directly reads FilterGroup state, update it to the new nested structure.**
- **Database driver fail-fast** — FindInSetFilter and RegexFilter now throw `UnsupportedDatabaseDriverException` on unsupported drivers (e.g., SQLite) instead of generating invalid SQL silently. FullTextFilter runs in degraded mode on SQLite with a warning log.

### Added — 7 New Filter Types

- **SoftDeleteFilter** — built-in soft delete visibility control (select/radio/toggle modes)
- **ExistsFilter** — filter by relationship existence (`whereHas`/`whereDoesntHave`), with `constrainedBy()` and static factories `forExists()`/`forNotExists()`
- **AggregateFilter** — filter by relationship aggregate values (`withCount`/`withSum`/`withAvg`/`withMax`/`withMin` + `having`), with `countOf()`/`sumOf()` factories
- **ColumnCompareFilter** — compare two database columns (`whereColumn`), toggle or operator-select mode
- **AdvancedJsonFilter** — structural JSON queries: `arrayContains()`, `pathExists()`, `hasKey()` with per-driver SQL (MySQL `JSON_CONTAINS`, PostgreSQL `@>`, SQLite `json_each` degraded)
- **TimezoneAwareDateFilter** — date range filtering with automatic user↔database timezone conversion via Carbon, supports `string|Closure` for dynamic user timezone
- **MorphRelationFilter** — polymorphic relationship filtering: `morphTo()` mode for type selection, `morphToMany()` mode for record selection with multiple support

### Added — Infrastructure

- **FilterStateDescriptor** — declarative state protocol for all 29 filters. Each filter implements `describeState()` returning fields, state type (`Single`/`Multiple`/`Range`/`Toggle`/`Keyed`/`Composite`), capabilities, and database support
- **StateType enum** — `Single`, `Multiple`, `Range`, `Toggle`, `Keyed`, `Composite`
- **HasFilterState trait** — `getStateDescriptor()`/`isStateEmpty()` on all filters
- **`php artisan dcat-filters:matrix`** — auto-generates capability matrix (markdown/json) from filter descriptors
- **HasModelOptions trait** — extracted shared `modelClass`/`titleColumn`/`keyColumn`/`multiple` properties + `resolveModelDisplayName()` from SelectTableFilter, ModalSelectFilter, MorphRelationFilter
- **HasSortPresets trait** — sort preset definitions (single/multi-field), `applySortPreset()`/`resetSortPreset()`/`getSortPresetActions()`/`exportSortState()`/`importSortState()`
- **Nested relationship support** — `->relationship('author.company.country', 'name')` uses Laravel's native nested `whereHas`

### Fixed

- **[Critical] FilterGroup field collision** — child filter form fields isolated in Fieldset with `statePath` namespacing
- **[High] ModalSelectFilter::relationship() UI broken** — `relationship()` now accepts optional `$modelClass`, triggers `configureForm()`/`configureQuery()`
- **[High] SelectTableFilter::relationship() empty dropdown** — `relationship()` now accepts optional `$modelClass`, triggers `configureForm()`
- **[High] SelectTableFilter query not applied** — `configureQuery()` now uses `parent::modifyQueryUsing()` to avoid method override conflict with Filament's internal `$modifyQueryUsing` property
- **[Medium] ModalSelectFilter labels protocol** — Controller returns ordered array matching input IDs, frontend uses `Array.isArray()` with null fallback
- **[Medium] RegexFilter false rejection of `/`** — delimiter changed from `/` to `\x01`
- **GeoLocationFilter** — coordinate range validation (lat -90~90, lon -180~180) and positive radius check
- **RegexFilter** — `@preg_match` pre-validation rejects invalid patterns before DB execution
- **CascadingSelectFilter** — batch query grouped by `keyColumn|titleColumn` for same-model different-column configs
- **ModalSelectFilter indicator** — `select()` limits columns in indicator queries
- **FilterGroup nesting depth** — recursive validation, max 5 levels, throws `InvalidArgumentException`
- **HasFilterExportImport** — descriptor-driven export skips empty filters, import validates field names

### Changed

- **FilterGroup** — `applyFilterQuery()`/`configureQuery()` driven by child filter's `getStateDescriptor()` instead of field-name guessing
- **HasDatabaseDriver** — new `assertDriverSupported()` method with three-tier check (supported → degraded → unsupported)
- **FilterStateDescriptor** — added `degradedSupport()`/`getDegradedSupport()` for drivers with reduced functionality
- **Config** — database driver comment clarifies MySQL/PostgreSQL/SQLite only; SQL Server/Oracle not supported
- **Facade + FilamentDcatFilters** — 31 factory methods (29 filter types + `version()` + `config()`)
- **MakeDcatFilterCommand** — supports all 29 filter types

### Documentation

- 29 filter capability matrix (EN + CN), auto-generated by `dcat-filters:matrix`
- 7 new filter usage docs (EN + CN): soft-delete, exists, aggregate, column-compare, advanced-json, timezone-date, morph-relation
- HasRelationship docs updated with nested path examples
- feature-analysis, package-structure, CLAUDE.md updated to 29 filters / 18 traits
- i18n: en, zh_CN, zh_TW — all new filter translation keys

### Tests

- Total: **1064 tests** with **1559 assertions** (all passing)
- New: FilterStateDescriptor unit tests + 29-filter descriptor consistency tests
- New: Real SQLite query execution tests (21 tests: basic/combination/relationship/fail-fast)
- New: Nested relationship integration tests
- New: Performance baseline tests (500 rows: query count, memory < 10MB)
- New: HasSortPresets tests (10 tests)
- New: 7 filter unit tests + query behavior tests

## v1.3.0 - 2026-03-17

### Added

- **RelativeDateFilter custom presets** — `addPresets()` supports custom date ranges via closures
- **FilterGroup array value support** — child filters with array-based form data (e.g., RangeFilter from/to) now handled correctly
- **93 new tests** — comprehensive query behavior tests for 11 uncovered filters, Concerns trait method tests, ModalSelectController HTTP integration tests

### Fixed

- **Security: XSS in modal-select Blade** — replaced raw output with `@json()` encoding
- **Security: Routes** — added `throttle:60,1` middleware to modal select routes
- **Security: ModalSelectController** — added `max:100` validation on `ids` array
- **Security: JsonFilter** — fixed regex character class for `-` in JSON path validation
- **Security: InputMaskFilter** — added null coalescing to `preg_replace()` for safety
- **HasFilterExportImport** — removed double URL decode in `loadFiltersFromUrl()` (corrupted `+` characters)
- **HasFilterExportImport** — encryption failure now returns `false` instead of silent fallback
- **FindInSetFilter** — 6 methods now properly call `configureForm()` to rebuild form on option changes
- **SyncsFiltersToUrlWithoutHistory** — hardcoded `history: false` to avoid PHP trait property conflict
- **filter-persistence.js** — added deduplication guard to prevent multiple Livewire hook registrations
- **zh_CN language** — added 2 missing accessibility translation keys

### Changed

- **HasDatabaseDriver** — removed unused `isMysql()`/`isSqlite()` methods
- **HasColumnName** — added `isValueEmpty()` utility method
- **FilamentDcatFilters::version()** — now uses `Composer\InstalledVersions` for accurate version
- **Config cleanup** — removed dead `session_enabled` key, added `modal_select.pagination_options` and `regex.max_pattern_length`
- **HasSelectRadioDisplay** — added unified `buildFormComponent()` method
- **HasInlineLabel** — uses `method_exists()` instead of `instanceof` for broader compatibility
- **Blade views** — added ARIA labels for accessibility, Alpine `destroy()` cleanup, `AbortController` for fetch

### Documentation

- Updated test counts across all docs (786 tests, 1145 assertions)
- Added 10 missing trait documentations to concerns-traits.md (EN + CN)
- Added regex `max_pattern_length` config docs
- Added FilterGroup multi-field array value support docs
- Marked partially-implemented items in future-improvements.md
- Fixed package-structure.md: 16 traits, zh_TW in lang listing

### Tests

- Total: **786 tests** with **1145 assertions** (all passing)
- New: Query behavior tests for ScopeFilter, FullTextFilter, EnumFilter, DateComponentFilter, RegexFilter, JsonFilter, FindInSetFilter, GeoLocationFilter, RelativeDateFilter, InputMaskFilter, FilterGroup
- New: HasRangeQuery.applyRangeQuery(), HasRelationship constraint/whereIn, HasFilterExportImport encryption roundtrip
- New: HasInlineLabel component tests, HasScopeBadgeCounts format/cache, HasColumnName.isValueEmpty()
- New: ModalSelectController HTTP tests (400/401/403/200 full coverage)

## v1.2.0 - 2026-03-17

### Added

- **HasSelectRadioDisplay trait** — extracted shared select/radio display style switching from BooleanFilter and NullFilter
- **HasColumnName trait** added to BooleanFilter, NullFilter, InputMaskFilter — enables custom column name mapping via `column()`
- **Artisan command expanded** — `make:dcat-filter` now supports all 22 filter types (added between, scope, date-component, select-table, modal-select, hidden, relative-date, cascading-select, find-in-set, input-mask, geo-location, filter-group)
- **HasOperator trait** — extracted shared operator logic (gt, gte, lt, lte, eq, ne) with validation
- **HasLabelResolver trait** — unified 3 different label resolution patterns across 14+ filters
- **49 SQL query behavior tests** — verify actual SQL generation for BooleanFilter, NullFilter, ComparisonFilter, InFilter, LikeFilter, HiddenFilter, BetweenFilter, RangeFilter
- **Facade IDE helper** — PHPDoc annotations for all filter factory methods
- **Filter export/import** — mergeFilters() now handles encrypted filter data

### Fixed

- **Security: ModalSelectController** — empty `allowed_models` config now denies all requests instead of allowing all
- **Security: GeoLocationFilter** — SQL column names now use `grammar->wrap()` to prevent injection
- **Security: Routes** — modal select routes now use `auth` middleware
- **FilterGroup** — properly delegates to child filter `apply()` instead of hardcoded WHERE
- **SelectTableFilter** — `modifyQueryUsing` callback now correctly applied in query
- **RangeFilter::toTimestamp()** — fixed zero-value bug (`if ($from)` → `isRangeValueEmpty()`)
- **HasRangeQuery** — zero-value bug fix for range filtering (treats "0" as valid)
- **SyncsFiltersToUrl** — replaced `#[Url]` attributes with `queryString()` for dynamic history control

### Changed

- **HasRangeQuery indicators** — internationalized hardcoded "from"/"to" strings with translation keys
- **JsonFilter** — renamed `VALID_OPERATORS` to `ALLOWED_OPERATORS` for consistency with HasOperator
- **SyncsFiltersToUrlWithoutHistory** — simplified to thin wrapper overriding `queryString()`
- **LikeFilter** — improved `wildcardPosition` documentation comments
- **PersistsFiltersInSession** — reads `session_prefix` from config instead of hardcoding
- **filter-persistence.js** — updated from Livewire 2 `message.processed` to Livewire 3 `commit` hook

### Removed

- Unused `SelectTableFilter::$searchColumns` property and `searchable()` method
- Unused `RelativeDateFilter::$includeCustomRange` property
- Unused `database.case_insensitive` config key
- Dead `SyncsFiltersToUrl::withoutUrlHistory()` method
- Dead code in SelectTableFilter (tableColumns, modalWidth, getModel)
- Redundant `BetweenFilter::label()` override

### Tests

- Total: **620 tests** with **887 assertions** (at release time)
- New: SQL query behavior tests, HasColumnName tests, HasOperator tests, Facade tests, Artisan command tests, Architecture tests

## v1.1.3 - 2026-02-27

### What's Changed

#### Bug Fixes

- Remove dash prefix (`—`) from RangeFilter's "to" field inline label

## v1.1.2 - 2026-02-25

### Fixed

- Fix failing FullTextFilter test after prefixIcon removal

## v1.1.1 - 2026-02-25

### Fixed

- Removed unnecessary magnifying glass `prefixIcon` from `FullTextFilter`, which conflicted with the inline label prefix

## v1.1.0 - Dcat Admin Style Inline Labels - 2026-02-25

### What's New

#### Dcat Admin Style Inline Labels

All filter labels now display **inside the input as a prefix** (dcat-admin style) instead of above the input (Filament default). Placeholder text defaults to the label text.

**Before (Filament default):**

```
Label
[ placeholder       ]





```
**After (dcat-admin style):**

```
[ Label | label text ]





```
#### New Features

- **`HasInlineLabel` trait** — reusable trait for all filters
  
- **Config options:**
  
  - `inline_label` (default `true`) — enable/disable globally
  - `placeholder_from_label` (default `true`) — use label text as placeholder
  
- **Per-filter opt-out:** `->inlineLabel(false)`
  
- **Range filters:** label on "from" field, dash `—` separator on "to" field
  

#### Affected Filters (20 total)

All filters with visible UI components:
`LikeFilter`, `FullTextFilter`, `ComparisonFilter`, `InFilter`, `EnumFilter`, `BooleanFilter`, `NullFilter`, `RangeFilter`, `BetweenFilter`, `DateComponentFilter`, `RelativeDateFilter`, `InputMaskFilter`, `ScopeFilter`, `JsonFilter`, `FindInSetFilter`, `RegexFilter`, `GeoLocationFilter`, `CascadingSelectFilter`, `SelectTableFilter`, `ModalSelectFilter`

Correctly skipped: `FilterGroup` (no own form), `HiddenFilter` (no UI)

#### Backward Compatibility

Fully backward compatible. To restore Filament default behavior:

```php
// config/filament-dcat-filters.php
'inline_label' => false,

// Or per-filter
LikeFilter::make('name')->inlineLabel(false)





```
## v1.0.8 - 2026-02-05

### Added

- **RangeFilter datetime() end time default**: Automatically set end time to 23:59:59 when user selects a date
  - When user selects a date in the "to" datetime picker, if time is 00:00:00, it auto-adjusts to 23:59:59
  - Uses `afterStateUpdated` to handle the conversion after user interaction
  - Ensures all records of the selected end day are included in query results
  

## v1.0.7 - 2026-02-04

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

## v1.0.3 - 2026-01-21

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

## v1.0.2 - 2025-01-11

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

## v1.0.1 - 2025-01-10

### Fixed

- Fixed ModalSelectFilter hidden input binding to Livewire state
- Improved ModalSelectFilter UI with row click selection
- Added label display to ModalSelectFilter view
- Changed date display format to Y-m-d in DatePicker
- Added displayFormat to DatePicker to hide time in date-only filters


---

## v1.0.0 - 2025-11-17

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
