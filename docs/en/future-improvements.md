# Future Improvements Guide

This document outlines planned improvements and features that are not yet implemented but are recommended for future development.

## Table of Contents

1. [Reset All Filters Button](#reset-all-filters-button)
2. [Filter State Persistence](#filter-state-persistence)
3. [URL Query Parameter Sync](#url-query-parameter-sync)
4. [Cascading Filter Dependencies](#cascading-filter-dependencies)
5. [Accessibility Improvements](#accessibility-improvements)
6. [Comprehensive Test Coverage](#comprehensive-test-coverage)

---

## Reset All Filters Button

### Current Limitation

Currently, users must clear each filter individually. There is no single "Reset All" button to clear all active filters at once.

### Recommended Implementation

Add a custom table action to reset all filters:

```php
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->filters([...])
        ->headerActions([
            Action::make('resetFilters')
                ->label(__('Reset All Filters'))
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->action(function ($livewire) {
                    $livewire->tableFilters = [];
                    $livewire->resetTable();
                })
                ->visible(fn ($livewire) => count(array_filter($livewire->tableFilters)) > 0),
        ]);
}
```

### Alternative: Using Filament's Built-in Feature

Filament v4 provides a built-in filter reset. You can customize its appearance:

```php
->filtersFormColumns(3)
->persistFiltersInSession()
```

---

## Filter State Persistence

### Current Limitation

Filter state is lost when the page is refreshed. Users lose their filtering work.

### Recommended Implementation

#### Option 1: Session Persistence (Built-in)

Filament provides built-in session persistence:

```php
public function table(Table $table): Table
{
    return $table
        ->filters([...])
        ->persistFiltersInSession();
}
```

#### Option 2: LocalStorage Persistence

For client-side persistence that survives session expiry:

```javascript
// resources/js/filter-persistence.js
document.addEventListener('livewire:init', () => {
    const storageKey = 'filament-table-filters';

    // Save filters on change
    Livewire.hook('message.processed', (message, component) => {
        if (component.fingerprint.name.includes('ListRecords')) {
            const filters = component.serverMemo.data.tableFilters;
            localStorage.setItem(storageKey, JSON.stringify(filters));
        }
    });

    // Restore filters on page load
    Livewire.hook('component.initialized', (component) => {
        if (component.fingerprint.name.includes('ListRecords')) {
            const savedFilters = localStorage.getItem(storageKey);
            if (savedFilters) {
                component.call('setTableFilters', JSON.parse(savedFilters));
            }
        }
    });
});
```

#### Option 3: Database Persistence

For user-specific filter preferences:

```php
// Create a user preferences table/model
Schema::create('user_filter_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('resource');
    $table->json('filters');
    $table->timestamps();
});

// In your Resource
public function mount(): void
{
    parent::mount();

    $savedFilters = UserFilterPreference::where('user_id', auth()->id())
        ->where('resource', static::class)
        ->first();

    if ($savedFilters) {
        $this->tableFilters = $savedFilters->filters;
    }
}

public function updatedTableFilters(): void
{
    UserFilterPreference::updateOrCreate(
        ['user_id' => auth()->id(), 'resource' => static::class],
        ['filters' => $this->tableFilters]
    );
}
```

---

## URL Query Parameter Sync

### Current Limitation

Filters don't update the browser URL, making it impossible to bookmark or share filtered views.

### Recommended Implementation

#### Option 1: Livewire URL Sync

```php
use Livewire\Attributes\Url;

class ListPosts extends ListRecords
{
    #[Url]
    public array $tableFilters = [];

    #[Url]
    public ?string $tableSearch = null;

    #[Url]
    public ?string $tableSortColumn = null;

    #[Url]
    public ?string $tableSortDirection = null;
}
```

#### Option 2: Custom URL Sync Trait

Create a reusable trait:

```php
<?php

namespace App\Concerns;

use Livewire\Attributes\Url;

trait SyncsFiltersToUrl
{
    #[Url(except: [])]
    public array $tableFilters = [];

    public function getFilterQueryString(): array
    {
        return collect($this->tableFilters)
            ->filter(fn ($value) => !empty($value))
            ->mapWithKeys(fn ($value, $key) => ["filter[{$key}]" => $value])
            ->toArray();
    }

    public function applyFiltersFromUrl(): void
    {
        $filters = request()->query('filter', []);

        foreach ($filters as $key => $value) {
            $this->tableFilters[$key] = $value;
        }
    }

    public function mount(): void
    {
        parent::mount();
        $this->applyFiltersFromUrl();
    }
}
```

Usage:

```php
class ListPosts extends ListRecords
{
    use SyncsFiltersToUrl;

    // ...
}
```

---

## Cascading Filter Dependencies

### Current Limitation

Filter options cannot depend on other filter values. For example, you cannot have a Country → State → City cascade.

### Recommended Implementation

#### Using Livewire Reactive Properties

```php
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Tables\Filters\Filter;

Filter::make('location')
    ->form([
        Select::make('country_id')
            ->label('Country')
            ->options(Country::pluck('name', 'id'))
            ->live()
            ->afterStateUpdated(fn (callable $set) => $set('state_id', null)),

        Select::make('state_id')
            ->label('State')
            ->options(function (Get $get) {
                $countryId = $get('country_id');

                if (!$countryId) {
                    return [];
                }

                return State::where('country_id', $countryId)
                    ->pluck('name', 'id');
            })
            ->live()
            ->afterStateUpdated(fn (callable $set) => $set('city_id', null)),

        Select::make('city_id')
            ->label('City')
            ->options(function (Get $get) {
                $stateId = $get('state_id');

                if (!$stateId) {
                    return [];
                }

                return City::where('state_id', $stateId)
                    ->pluck('name', 'id');
            }),
    ])
    ->query(function (Builder $query, array $data): Builder {
        return $query
            ->when($data['country_id'], fn ($q, $v) => $q->where('country_id', $v))
            ->when($data['state_id'], fn ($q, $v) => $q->where('state_id', $v))
            ->when($data['city_id'], fn ($q, $v) => $q->where('city_id', $v));
    });
```

#### Creating a CascadingSelectFilter Class

```php
<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CascadingSelectFilter extends Filter
{
    protected array $levels = [];

    /**
     * Add a cascade level.
     *
     * @param string $name Field name
     * @param string $label Display label
     * @param string $model Model class
     * @param string|null $parentField Parent field name (null for root)
     * @param string $foreignKey Foreign key column
     * @param string $titleColumn Display column
     */
    public function addLevel(
        string $name,
        string $label,
        string $model,
        ?string $parentField = null,
        string $foreignKey = 'parent_id',
        string $titleColumn = 'name'
    ): static {
        $this->levels[] = [
            'name' => $name,
            'label' => $label,
            'model' => $model,
            'parentField' => $parentField,
            'foreignKey' => $foreignKey,
            'titleColumn' => $titleColumn,
        ];

        $this->rebuildForm();

        return $this;
    }

    protected function rebuildForm(): void
    {
        $fields = [];
        $previousField = null;

        foreach ($this->levels as $index => $level) {
            $field = Select::make($level['name'])
                ->label($level['label'])
                ->options(function (Get $get) use ($level, $previousField) {
                    $model = $level['model'];

                    if ($previousField) {
                        $parentValue = $get($previousField);

                        if (!$parentValue) {
                            return [];
                        }

                        return $model::where($level['foreignKey'], $parentValue)
                            ->pluck($level['titleColumn'], 'id');
                    }

                    return $model::pluck($level['titleColumn'], 'id');
                })
                ->native(false)
                ->live();

            // Clear dependent fields when this field changes
            $dependentFields = array_slice(
                array_column($this->levels, 'name'),
                $index + 1
            );

            if (!empty($dependentFields)) {
                $field->afterStateUpdated(function (callable $set) use ($dependentFields) {
                    foreach ($dependentFields as $fieldName) {
                        $set($fieldName, null);
                    }
                });
            }

            $fields[] = $field;
            $previousField = $level['name'];
        }

        $this->form($fields);
        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            foreach ($this->levels as $level) {
                $value = $data[$level['name']] ?? null;

                if ($value !== null && $value !== '') {
                    $query->where($level['name'], $value);
                }
            }

            return $query;
        });
    }
}
```

Usage:

```php
CascadingSelectFilter::make('location')
    ->addLevel('country_id', 'Country', Country::class)
    ->addLevel('state_id', 'State', State::class, 'country_id', 'country_id')
    ->addLevel('city_id', 'City', City::class, 'state_id', 'state_id');
```

---

## Accessibility Improvements

### Current Limitation

Missing ARIA labels, role attributes, and screen reader descriptions.

### Recommended Implementation

#### 1. Add ARIA Labels to Filter Components

Update Blade templates:

```blade
{{-- Example: modal-select.blade.php --}}
<div
    role="combobox"
    aria-haspopup="dialog"
    aria-expanded="false"
    aria-label="{{ $label }}"
    x-data="..."
>
    <button
        type="button"
        @click="openModal($event)"
        aria-describedby="filter-{{ $filterName }}-description"
        class="..."
    >
        <span class="sr-only">{{ __('Open selection dialog for :label', ['label' => $label]) }}</span>
        ...
    </button>

    <span id="filter-{{ $filterName }}-description" class="sr-only">
        {{ __('Currently selected: :count items', ['count' => count($selected)]) }}
    </span>
</div>
```

#### 2. Keyboard Navigation Support

```javascript
// Add keyboard navigation to modal
x-data="{
    ...
    handleKeydown(event) {
        switch (event.key) {
            case 'Escape':
                this.cancel();
                break;
            case 'Enter':
                if (event.ctrlKey || event.metaKey) {
                    this.confirm();
                }
                break;
            case 'ArrowDown':
                this.focusNextRow();
                break;
            case 'ArrowUp':
                this.focusPreviousRow();
                break;
        }
    },
    focusNextRow() {
        // Implementation
    },
    focusPreviousRow() {
        // Implementation
    }
}"
@keydown="handleKeydown($event)"
```

#### 3. Screen Reader Announcements

```javascript
// Announce filter changes to screen readers
function announceToScreenReader(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('role', 'status');
    announcement.setAttribute('aria-live', 'polite');
    announcement.setAttribute('aria-atomic', 'true');
    announcement.className = 'sr-only';
    announcement.textContent = message;
    document.body.appendChild(announcement);

    setTimeout(() => announcement.remove(), 1000);
}

// Usage
updateSelection(selected, ...) {
    this.selected = selected;
    announceToScreenReader(`${selected.length} items selected`);
}
```

#### 4. Focus Management

```javascript
// Trap focus within modal
x-trap.inert.noscroll="open"

// Return focus to trigger after modal closes
openModal(event) {
    this.triggerElement = event.target;
    this.open = true;
},
cancel() {
    this.open = false;
    this.$nextTick(() => {
        this.triggerElement?.focus();
    });
}
```

---

## Comprehensive Test Coverage

### Current Limitation

Only architecture tests exist. No unit or feature tests for filter functionality.

### Recommended Test Structure

```
tests/
├── Feature/
│   ├── Filters/
│   │   ├── RangeFilterTest.php
│   │   ├── LikeFilterTest.php
│   │   ├── InFilterTest.php
│   │   ├── ComparisonFilterTest.php
│   │   ├── ScopeFilterTest.php
│   │   ├── ModalSelectFilterTest.php
│   │   ├── SelectTableFilterTest.php
│   │   ├── DateComponentFilterTest.php
│   │   └── HiddenFilterTest.php
│   └── Controllers/
│       └── ModalSelectControllerTest.php
└── Unit/
    ├── Concerns/
    │   └── HasRangeQueryTest.php
    └── Components/
        └── ModalSelectTableTest.php
```

### Example Tests

#### RangeFilterTest.php

```php
<?php

use Cooper\FilamentDcatFilters\Filters\RangeFilter;
use Illuminate\Database\Eloquent\Builder;

it('applies date range filter correctly', function () {
    $filter = RangeFilter::make('created_at')->date();

    $query = Post::query();
    $data = ['from' => '2024-01-01', 'to' => '2024-12-31'];

    $result = $filter->apply($query, $data);

    expect($result->toSql())->toContain('between');
});

it('swaps from and to when from is greater than to', function () {
    $filter = RangeFilter::make('amount')->numeric();

    $query = Post::query();
    $data = ['from' => 100, 'to' => 50];

    $result = $filter->apply($query, $data);

    // Should swap to [50, 100]
    expect($result->getBindings())->toContain(50, 100);
});

it('treats zero as valid value', function () {
    $filter = RangeFilter::make('quantity')->integer();

    $query = Product::query();
    $data = ['from' => 0, 'to' => 10];

    $result = $filter->apply($query, $data);

    expect($result->getBindings())->toContain(0);
});

it('handles empty values correctly', function () {
    $filter = RangeFilter::make('price')->numeric();

    $query = Product::query();
    $originalSql = $query->toSql();

    $result = $filter->apply($query, ['from' => null, 'to' => null]);

    expect($result->toSql())->toBe($originalSql);
});
```

#### LikeFilterTest.php

```php
<?php

use Cooper\FilamentDcatFilters\Filters\LikeFilter;

it('escapes special LIKE characters', function () {
    $filter = LikeFilter::make('title');

    $query = Post::query();
    $data = ['value' => '50%'];

    $result = $filter->apply($query, $data);

    expect($result->getBindings()[0])->toContain('\\%');
});

it('applies case insensitive search', function () {
    $filter = LikeFilter::make('name')->insensitive();

    $query = User::query();
    $data = ['value' => 'John'];

    $result = $filter->apply($query, $data);

    expect($result->toSql())->toContain('LOWER');
});

it('applies wildcard at correct position', function () {
    $filter = LikeFilter::make('email')->startsWith();

    $query = User::query();
    $data = ['value' => 'admin'];

    $result = $filter->apply($query, $data);

    expect($result->getBindings()[0])->toBe('admin%');
});
```

#### ModalSelectControllerTest.php

```php
<?php

use App\Models\User;
use Cooper\FilamentDcatFilters\Http\Controllers\ModalSelectController;

it('returns labels for valid model', function () {
    $users = User::factory()->count(3)->create();

    $response = $this->postJson(route('filament-dcat-filters.fetch-labels'), [
        'model' => User::class,
        'ids' => $users->pluck('id')->toArray(),
        'column' => 'name',
        'keyColumn' => 'id',
    ]);

    $response->assertOk();
    $response->assertJsonCount(3, 'labels');
});

it('rejects invalid model class', function () {
    $response = $this->postJson(route('filament-dcat-filters.fetch-labels'), [
        'model' => 'InvalidClass',
        'ids' => [1],
        'column' => 'name',
    ]);

    $response->assertStatus(400);
});

it('rejects unauthorized model access', function () {
    config(['filament-dcat-filters.allowed_models' => [User::class]]);

    $response = $this->postJson(route('filament-dcat-filters.fetch-labels'), [
        'model' => Post::class,
        'ids' => [1],
        'column' => 'title',
    ]);

    $response->assertStatus(403);
});

it('respects rate limiting', function () {
    for ($i = 0; $i < 65; $i++) {
        $response = $this->postJson(route('filament-dcat-filters.fetch-labels'), [
            'model' => User::class,
            'ids' => [1],
            'column' => 'name',
        ]);
    }

    $response->assertStatus(429);
});
```

### Running Tests

```bash
# Run all tests
php artisan test

# Run filter tests only
php artisan test --filter=Filter

# Run with coverage
php artisan test --coverage --min=80
```

---

## Implementation Priority

| Feature | Priority | Complexity | Impact |
|---------|----------|------------|--------|
| Test Coverage | High | Medium | High |
| Accessibility | High | Medium | High |
| URL Query Sync | Medium | Low | Medium |
| Filter Persistence | Medium | Low | Medium |
| Reset All Button | Low | Low | Low |
| Cascading Filters | Low | High | Medium |

---

## Contributing

If you'd like to implement any of these features, please:

1. Open an issue to discuss the approach
2. Create a feature branch
3. Write tests for new functionality
4. Submit a pull request

We welcome contributions!
