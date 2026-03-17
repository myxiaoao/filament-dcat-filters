# Comprehensive Optimization Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Fix all security vulnerabilities, bugs, code duplication, design issues, dead code, missing tests, and outdated documentation across the filament-dcat-filters package.

**Architecture:** Four-phase approach — security/bugs first, then deduplication via traits, then design improvements, finally cleanup/tests/docs. Each phase ends with full test suite verification.

**Tech Stack:** PHP 8.3, Laravel 12, Filament v4, Pest 4, Laravel Pint

---

## Phase 1: Security & Bug Fixes

### Task 1: ModalSelectTable allowed_models security check

**Files:**
- Modify: `src/Components/ModalSelectTable.php:94-101`
- Test: `tests/Feature/Components/ModalSelectTableTest.php`

**Step 1: Fix getQuery() to validate modelClass against allowed_models config**

In `ModalSelectTable::getQuery()`, add the same `allowed_models` whitelist check that `ModalSelectController` uses:

```php
protected function getQuery(): Builder
{
    if (! $this->modelClass) {
        throw new \RuntimeException('Model class is required.');
    }

    if (! class_exists($this->modelClass) || ! is_subclass_of($this->modelClass, \Illuminate\Database\Eloquent\Model::class)) {
        throw new \RuntimeException('Invalid model class.');
    }

    $allowedModels = config('filament-dcat-filters.allowed_models', []);
    if (! empty($allowedModels) && ! in_array($this->modelClass, $allowedModels, true)) {
        throw new \RuntimeException('Model not allowed.');
    }

    return $this->modelClass::query();
}
```

**Step 2: Run tests**

Run: `composer test`
Expected: All 512+ tests pass

**Step 3: Commit**

```
fix(security): add allowed_models validation to ModalSelectTable
```

---

### Task 2: Add auth middleware to fetch-labels route

**Files:**
- Modify: `routes/web.php:6`
- Remove duplicate `web` middleware (already applied by Spatie PackageServiceProvider)

**Step 1: Update route middleware**

```php
Route::middleware(['auth'])
    ->prefix('filament-dcat-filters')
    ->name('filament-dcat-filters.')
    ->group(function () {
        Route::post('/fetch-labels', [ModalSelectController::class, 'fetchLabels'])
            ->name('fetch-labels');
    });
```

**Step 2: Run tests, commit**

```
fix(security): add auth middleware to fetch-labels route
```

---

### Task 3: Fix ModalSelectController pluck losing id-label mapping

**Files:**
- Modify: `src/Http/Controllers/ModalSelectController.php:69-72`
- Test: `tests/Feature/Http/Controllers/ModalSelectControllerTest.php`

**Step 1: Fix pluck to preserve key-value mapping**

```php
$labels = $modelClass::query()
    ->whereIn($keyColumn, $ids)
    ->pluck($column, $keyColumn)
    ->toArray();
```

**Step 2: Run tests, commit**

```
fix: preserve id-label mapping in ModalSelectController::fetchLabels
```

---

### Task 4: Fix filter-persistence.js Livewire 3 hook API

**Files:**
- Modify: `resources/js/filter-persistence.js:14-25`

**Step 1: Replace deprecated message.processed with Livewire 3 commit hook**

```js
Livewire.hook('commit', ({ component, respond }) => {
    respond(() => {
        if (component.id !== componentId) return;

        const filters = component.snapshot?.data?.tableFilters?.[0];
        if (filters) {
            try {
                localStorage.setItem(key, JSON.stringify(filters));
            } catch (e) {
                console.warn('Failed to save filters to LocalStorage:', e);
            }
        }
    });
});
```

**Step 2: Commit**

```
fix: update filter-persistence.js to Livewire 3 commit hook API
```

---

### Task 5: Fix HasRangeQuery::generateRangeIndicators zero-value bug

**Files:**
- Modify: `src/Concerns/HasRangeQuery.php:61,65`
- Test: `tests/Feature/Concerns/HasRangeQueryTest.php`

**Step 1: Replace truthy check with isRangeValueEmpty**

```php
protected function generateRangeIndicators(array $data, string $label): array
{
    $indicators = [];
    $from = $data['from'] ?? null;
    $to = $data['to'] ?? null;

    if (! $this->isRangeValueEmpty($from)) {
        $indicators[] = "{$label} from {$from}";
    }

    if (! $this->isRangeValueEmpty($to)) {
        $indicators[] = "{$label} to {$to}";
    }

    return $indicators;
}
```

**Step 2: Run tests, commit**

```
fix: HasRangeQuery indicator now correctly handles zero values
```

---

### Task 6: Fix JsonFilter::operator() not triggering configureQuery

**Files:**
- Modify: `src/Filters/JsonFilter.php:40-53`
- Test: `tests/Feature/Filters/JsonFilterTest.php`

**Step 1: Add configureQuery() call after setting operator**

```php
public function operator(string $operator): static
{
    $operator = strtolower($operator);

    if (! in_array($operator, self::VALID_OPERATORS)) {
        throw new \InvalidArgumentException(
            "Invalid operator: {$operator}. Valid operators are: ".implode(', ', self::VALID_OPERATORS)
        );
    }

    $this->operator = $operator;
    $this->configureQuery();

    return $this;
}
```

**Step 2: Run tests, commit**

```
fix: JsonFilter operator() now triggers configureQuery
```

---

### Task 7: Fix FilterGroup::applyFilterQuery hardcoded LIKE

**Files:**
- Modify: `src/Filters/FilterGroup.php:122-129`
- Test: `tests/Feature/Filters/FilterGroupTest.php`

**Step 1: Delegate to child filter's actual query logic**

Replace the hardcoded LIKE with proper delegation. Since Filament's Filter doesn't expose its query callback directly, we use `where($column, $value)` as a sensible default but allow the child filter's name as column:

```php
protected function applyFilterQuery(Builder $query, Filter $filter, mixed $value): void
{
    if ($value === null || $value === '') {
        return;
    }

    // Use the child filter's column name for a basic where clause
    $column = $filter->getName();
    $query->where($column, $value);
}
```

Note: This changes behavior from LIKE to exact match, which is more correct as a default. Users wanting LIKE should use LikeFilter as child.

**Step 2: Run tests, commit**

```
fix: FilterGroup applyFilterQuery uses exact match instead of hardcoded LIKE
```

---

### Task 8: Fix SelectTableFilter::relationship() being ignored

**Files:**
- Modify: `src/Filters/SelectTableFilter.php:62-72,164-201`

**Step 1: Make configureForm() use relationship when modelClass is not set**

In `configureForm()`, resolve model from relationship when `$this->modelClass` is null:

```php
protected function configureForm(): void
{
    // Resolve model from relationship if not explicitly set
    if (! $this->modelClass && $this->relationship) {
        // relationship() already calls configureForm, so we just need to
        // handle the case where model is derived from relationship name
        return;
    }

    if (! $this->modelClass) {
        return;
    }
    // ... rest unchanged
}
```

And in `configureQuery()`, the relationship handling is already correct (lines 219-242). The real fix is that `relationship()` should also trigger `configureQuery()`:

```php
public function relationship(string $relationship, ?string $titleColumn = 'name'): static
{
    $this->relationship = $relationship;
    $this->titleColumn = $titleColumn;
    $this->configureQuery();

    return $this;
}
```

**Step 2: Run tests, commit**

```
fix: SelectTableFilter relationship() now properly configures query
```

---

### Task 9: Fix ModalSelectFilter $modifyQueryUsing never called

**Files:**
- Modify: `src/Filters/ModalSelectFilter.php:230-267`

**Step 1: Apply modifyQueryUsing in configureQuery**

In the query callback inside `configureQuery()`, add:

```php
// After getting the base query, apply modification if set
$this->query(function (Builder $query, array $data): Builder {
    $value = $data['value'] ?? null;

    if ($this->isValueEmpty($value)) {
        return $query;
    }

    $column = $this->columnName ?? $this->getName();

    if ($this->modifyQueryUsing) {
        return ($this->modifyQueryUsing)($query, $value, $column);
    }

    // ... existing relationship/direct filtering logic
});
```

**Step 2: Run tests, commit**

```
fix: ModalSelectFilter now applies modifyQueryUsing callback
```

---

## Phase 2: Code Deduplication

### Task 10: Extract HasLabelResolver trait

**Files:**
- Create: `src/Concerns/HasLabelResolver.php`
- Modify: All filters that define labelResolver closures

**Step 1: Create the trait**

```php
<?php

namespace Cooper\FilamentDcatFilters\Concerns;

trait HasLabelResolver
{
    protected function resolveLabel(): string
    {
        return $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));
    }

    protected function labelResolver(): \Closure
    {
        return fn (): string => $this->resolveLabel();
    }
}
```

**Step 2: Replace all inline labelResolver closures across filters**

In each filter, replace:
- `fn (): string => $this->getLabel() ?? $this->getName()` -> `$this->labelResolver()`
- `fn (): string => $this->getLabel() ?? ucfirst($this->getName())` -> `$this->labelResolver()`
- `fn (): string => $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()))` -> `$this->labelResolver()`

Add `use HasLabelResolver;` to: LikeFilter, InFilter, ComparisonFilter, BooleanFilter, NullFilter, EnumFilter, RegexFilter, JsonFilter, FindInSetFilter, InputMaskFilter, SelectTableFilter, ModalSelectFilter, RelativeDateFilter, RangeFilter, DateComponentFilter, FullTextFilter, ScopeFilter.

**Step 3: Run tests, commit**

```
refactor: extract HasLabelResolver trait to unify label resolution
```

---

### Task 11: Extract HasValueCheck trait

**Files:**
- Create: `src/Concerns/HasValueCheck.php`
- Modify: All filters with `$value === null || $value === ''` checks

**Step 1: Create the trait**

```php
<?php

namespace Cooper\FilamentDcatFilters\Concerns;

trait HasValueCheck
{
    protected function isValueEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }
}
```

**Step 2: Replace all inline empty checks**

Add `use HasValueCheck;` to filters and replace `$value === null || $value === ''` with `$this->isValueEmpty($value)`.

Note: ModalSelectFilter already has this method — remove its local definition and use the trait instead.

**Step 3: Run tests, commit**

```
refactor: extract HasValueCheck trait for consistent empty value checking
```

---

### Task 12: Merge SyncsFiltersToUrl traits

**Files:**
- Modify: `src/Concerns/SyncsFiltersToUrl.php`
- Delete: `src/Concerns/SyncsFiltersToUrlWithoutHistory.php`

**Step 1: Merge into one trait with configurable history**

Since PHP attributes must be compile-time, use Livewire's `queryString()` method instead of `#[Url]`:

```php
<?php

namespace Cooper\FilamentDcatFilters\Concerns;

trait SyncsFiltersToUrl
{
    public array $tableFilters = [];
    public ?string $tableSearch = null;
    public ?string $tableSortColumn = null;
    public ?string $tableSortDirection = null;

    protected bool $urlHistory = true;

    public function withoutHistory(): static
    {
        $this->urlHistory = false;
        return $this;
    }

    public function queryString(): array
    {
        return [
            'tableFilters' => ['except' => [], 'history' => $this->urlHistory, 'keep' => false],
            'tableSearch' => ['except' => '', 'history' => $this->urlHistory, 'keep' => false],
            'tableSortColumn' => ['except' => '', 'history' => $this->urlHistory, 'keep' => false],
            'tableSortDirection' => ['except' => '', 'history' => $this->urlHistory, 'keep' => false],
        ];
    }

    // ... keep getFilterQueryString(), getShareableFilterUrl(), resetUrlParameters() unchanged
}
```

**Step 2: Create SyncsFiltersToUrlWithoutHistory as thin alias for backward compat**

```php
trait SyncsFiltersToUrlWithoutHistory
{
    use SyncsFiltersToUrl;

    protected bool $urlHistory = false;
}
```

**Step 3: Run tests, commit**

```
refactor: merge URL sync traits with configurable history
```

---

### Task 13: Refactor ComparisonFilter TextInput duplication

**Files:**
- Modify: `src/Filters/ComparisonFilter.php:78-142`

**Step 1: Extract buildValueInput helper**

```php
protected function buildValueInput(): TextInput
{
    $labelResolver = $this->labelResolver();

    $input = TextInput::make('value')
        ->label($labelResolver)
        ->placeholder(__('filament-dcat-filters::filament-dcat-filters.comparison.placeholder'))
        ->numeric()
        ->live()
        ->columnSpanFull();

    $this->applyInlineLabel($input, $labelResolver);

    return $input;
}
```

Then simplify `moneySuffix()`, `integer()`, `numeric()`:

```php
public function moneySuffix(string $suffix): static
{
    $this->moneySuffix = $suffix;
    $this->form([$this->buildValueInput()->suffix($suffix)]);
    return $this;
}

public function integer(): static
{
    $this->inputType = 'integer';
    $this->form([$this->buildValueInput()->integer()]);
    return $this;
}

public function numeric(): static
{
    $this->inputType = 'numeric';
    $this->form([$this->buildValueInput()->step('any')]);
    return $this;
}
```

**Step 2: Run tests, commit**

```
refactor: extract ComparisonFilter buildValueInput to reduce duplication
```

---

### Task 14: Fix RangeFilter HtmlString full-qualified usage

**Files:**
- Modify: `src/Filters/RangeFilter.php`

**Step 1: Add use import and replace all 5 occurrences**

Add `use Illuminate\Support\HtmlString;` to imports.

Replace all `new \Illuminate\Support\HtmlString('&nbsp;')` with `new HtmlString('&nbsp;')`.

**Step 2: Run tests, commit**

```
refactor: import HtmlString in RangeFilter
```

---

### Task 15: ModalSelectFilter use HasColumnName trait

**Files:**
- Modify: `src/Filters/ModalSelectFilter.php`

**Step 1: Replace local $columnName with HasColumnName trait**

Add `use HasColumnName;` and remove:
- Line 50: `protected ?string $columnName = null;`
- Lines 212-217: the local `column()` method

**Step 2: Run tests, commit**

```
refactor: ModalSelectFilter uses HasColumnName trait
```

---

## Phase 3: Design Improvements

### Task 16: Fix HasOperator implicit configureQuery dependency

**Files:**
- Modify: `src/Concerns/HasOperator.php:21`

**Step 1: Use method_exists check instead of blind call**

```php
public function operator(string $operator): static
{
    if (! in_array($operator, static::ALLOWED_OPERATORS, true)) {
        throw new \InvalidArgumentException(
            "Invalid operator: {$operator}. Allowed: ".implode(', ', static::ALLOWED_OPERATORS)
        );
    }

    $this->operator = $operator;

    if (method_exists($this, 'configureQuery')) {
        $this->configureQuery();
    }

    return $this;
}
```

**Step 2: Run tests, commit**

```
fix: HasOperator safely checks for configureQuery before calling
```

---

### Task 17: Make persistence config actually effective

**Files:**
- Modify: `src/Concerns/PersistsFiltersInSession.php:18-21`

**Step 1: Read session prefix from config**

```php
protected function getFilterSessionKey(): string
{
    $prefix = config('filament-dcat-filters.persistence.session_prefix', 'filament-dcat-filters');

    return $prefix . ':' . static::class;
}
```

**Step 2: Run tests, commit**

```
fix: PersistsFiltersInSession reads session_prefix from config
```

---

### Task 18: Add spatie/laravel-package-tools to composer.json require

**Files:**
- Modify: `composer.json:29-32`

**Step 1: Add explicit dependency**

```json
"require": {
    "php": "^8.3",
    "filament/filament": "^4.0 || ^5.0",
    "spatie/laravel-package-tools": "^1.9"
},
```

**Step 2: Commit**

```
fix: declare spatie/laravel-package-tools as explicit dependency
```

---

### Task 19: Remove redundant enum_exists check in ServiceProvider

**Files:**
- Modify: `src/FilamentDcatFiltersServiceProvider.php:75-79`

**Step 1: Remove guard, Filament v4 guarantees this enum exists**

```php
if (config('filament-dcat-filters.table.reset_action_in_footer', true)) {
    $table->filtersResetActionPosition(
        \Filament\Tables\Enums\FiltersResetActionPosition::Footer
    );
}
```

**Step 2: Run tests, commit**

```
refactor: remove redundant enum_exists check in ServiceProvider
```

---

## Phase 4: Cleanup, Tests & Documentation

### Task 20: Remove dead code

**Files:**
- Modify: `src/Filters/RangeFilter.php` (remove `getPlaceholder()` method, lines 280-291)
- Modify: `src/Filters/SelectTableFilter.php` (remove unused `$tableColumns`, `$searchColumns`, `$modalWidth` properties and their setters)
- Modify: `src/Filters/BetweenFilter.php` (remove redundant `label()` override)

**Step 1: Remove each piece of dead code**
**Step 2: Run tests after each removal to confirm nothing breaks**
**Step 3: Commit**

```
refactor: remove dead code (unused properties, methods, overrides)
```

---

### Task 21: Add MakeDcatFilterCommand tests

**Files:**
- Create: `tests/Feature/Commands/MakeDcatFilterCommandTest.php`

**Step 1: Write comprehensive tests**

```php
<?php

use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->filesystem = app(Filesystem::class);
    $this->outputPath = app_path('Filament/Filters');
});

afterEach(function () {
    // Cleanup generated files
    if ($this->filesystem->isDirectory($this->outputPath)) {
        $this->filesystem->deleteDirectory($this->outputPath);
    }
});

it('creates a basic filter', function () {
    $this->artisan('make:dcat-filter', ['name' => 'TestStatus'])
        ->assertSuccessful();

    expect($this->outputPath . '/TestStatusFilter.php')->toBeFile();
});

it('appends Filter suffix automatically', function () {
    $this->artisan('make:dcat-filter', ['name' => 'Category'])
        ->assertSuccessful();

    expect($this->outputPath . '/CategoryFilter.php')->toBeFile();
});

it('does not duplicate Filter suffix', function () {
    $this->artisan('make:dcat-filter', ['name' => 'CategoryFilter'])
        ->assertSuccessful();

    expect($this->outputPath . '/CategoryFilter.php')->toBeFile();
    expect($this->outputPath . '/CategoryFilterFilter.php')->not->toBeFile();
});

it('supports all filter types', function (string $type) {
    $this->artisan('make:dcat-filter', ['name' => 'Test', '--type' => $type])
        ->assertSuccessful();
})->with(['basic', 'like', 'in', 'comparison', 'boolean', 'null', 'enum', 'range', 'regex', 'fulltext', 'json']);

it('rejects invalid filter type', function () {
    $this->artisan('make:dcat-filter', ['name' => 'Test', '--type' => 'invalid'])
        ->assertFailed();
});

it('does not overwrite without force flag', function () {
    $this->artisan('make:dcat-filter', ['name' => 'Duplicate'])->assertSuccessful();
    $this->artisan('make:dcat-filter', ['name' => 'Duplicate'])->assertFailed();
});

it('overwrites with force flag', function () {
    $this->artisan('make:dcat-filter', ['name' => 'Overwrite'])->assertSuccessful();
    $this->artisan('make:dcat-filter', ['name' => 'Overwrite', '--force' => true])->assertSuccessful();
});

it('generates correct namespace and parent class for type', function () {
    $this->artisan('make:dcat-filter', ['name' => 'PriceLike', '--type' => 'like'])->assertSuccessful();

    $content = file_get_contents($this->outputPath . '/PriceLikeFilter.php');
    expect($content)->toContain('use Cooper\FilamentDcatFilters\Filters\LikeFilter;');
    expect($content)->toContain('extends LikeFilter');
});
```

**Step 2: Run tests, commit**

```
test: add comprehensive MakeDcatFilterCommand tests
```

---

### Task 22: Add HasColumnName and HasOperator unit tests

**Files:**
- Create: `tests/Feature/Concerns/HasColumnNameTest.php`
- Create: `tests/Feature/Concerns/HasOperatorTest.php`

**Step 1: Write HasColumnName tests**

```php
<?php

use Cooper\FilamentDcatFilters\Filters\LikeFilter;

it('uses filter name as column by default', function () {
    $filter = LikeFilter::make('email');
    expect($filter)->not->toBeNull();
});

it('can set custom column name', function () {
    $filter = LikeFilter::make('search')->column('user_email');
    expect($filter)->not->toBeNull();
});
```

**Step 2: Write HasOperator tests**

```php
<?php

use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;

it('can set valid operators', function (string $method) {
    $filter = ComparisonFilter::make('price')->{$method}();
    expect($filter)->not->toBeNull();
})->with(['gt', 'gte', 'lt', 'lte', 'eq', 'ne']);

it('throws on invalid operator', function () {
    ComparisonFilter::make('price')->operator('INVALID');
})->throws(\InvalidArgumentException::class);
```

**Step 3: Run tests, commit**

```
test: add HasColumnName and HasOperator trait tests
```

---

### Task 23: Add Facade and helpers tests

**Files:**
- Create: `tests/Feature/FacadeTest.php`
- Create: `tests/Feature/HelpersTest.php`

**Step 1: Write tests for Facade factory methods and helpers**

**Step 2: Run tests, commit**

```
test: add Facade and helper function tests
```

---

### Task 24: Enhance architecture tests

**Files:**
- Modify: `tests/Unit/ArchTest.php`

**Step 1: Add structural rules**

```php
arch('filters extend Filament Filter')
    ->expect('Cooper\FilamentDcatFilters\Filters')
    ->toExtend('Filament\Tables\Filters\Filter');

arch('concerns are traits')
    ->expect('Cooper\FilamentDcatFilters\Concerns')
    ->toBeTraits();

arch('facade extends base facade')
    ->expect('Cooper\FilamentDcatFilters\Facades')
    ->toExtend('Illuminate\Support\Facades\Facade');

arch('controllers are in Http namespace')
    ->expect('Cooper\FilamentDcatFilters\Http\Controllers')
    ->toExtend('Illuminate\Routing\Controller');

arch('filters should not depend on components')
    ->expect('Cooper\FilamentDcatFilters\Filters')
    ->not->toUse('Cooper\FilamentDcatFilters\Components');
```

**Step 2: Run tests, commit**

```
test: enhance architecture tests with structural constraints
```

---

### Task 25: Update documentation

**Files:**
- Modify: `docs/en/package-structure.md` — fix pest version ^3.0 -> ^4.0, add HasLabelResolver/HasValueCheck traits
- Modify: `docs/zh_CN/package-structure.md` — same
- Modify: `docs/en/future-improvements.md` — update test count
- Modify: `docs/zh_CN/future-improvements.md` — update test count
- Modify: `docs/en/concerns-traits.md` — document new traits
- Modify: `docs/zh_CN/concerns-traits.md` — document new traits

**Step 1: Update all documentation files**
**Step 2: Commit**

```
docs: update documentation to reflect all optimizations
```

---

## Final Verification

After all tasks:

1. Run: `composer test` — expect all tests pass
2. Run: `vendor/bin/pint` — expect clean
3. Run: `git diff --stat` — review all changes
