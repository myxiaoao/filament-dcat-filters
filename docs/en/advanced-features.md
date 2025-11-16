# Advanced Features Implementation Guide

## API Data Source Support

API data source support allows filters to dynamically load option data from remote APIs.

### Implementation Approach

Filament natively supports asynchronous data loading, which can be implemented as follows:

```php
use Filament\Forms\Components\Select;

Select::make('user_id')
    ->label('User')
    ->searchable()
    ->getSearchResultsUsing(fn (string $search) =>
        User::where('name', 'like', "%{$search}%")
            ->limit(50)
            ->pluck('name', 'id')
    )
    ->getOptionLabelUsing(fn ($value): ?string =>
        User::find($value)?->name
    );
```

### InFilter with API Support

Extend `InFilter` to support API data sources:

```php
InFilter::make('category_id')
    ->label('Category')
    ->searchable()
    ->getSearchResultsUsing(function (string $search) {
        // Fetch data from API
        $response = Http::get('https://api.example.com/categories', [
            'search' => $search,
            'limit' => 50,
        ]);

        return collect($response->json('data'))
            ->pluck('name', 'id')
            ->toArray();
    })
    ->getOptionLabelUsing(function ($value) {
        // Get single option label
        $response = Http::get("https://api.example.com/categories/{$value}");
        return $response->json('data.name');
    });
```

### SelectTableFilter with API Support

`SelectTableFilter` can support APIs by modifying the `options()` method:

```php
SelectTableFilter::make('author_id')
    ->label('Author (API)')
    ->model(User::class)
    ->searchable()
    ->multiple()
    ->modifyQueryUsing(function ($query, string $search = '') {
        // Remote API call logic can be added here
        if ($search) {
            return $query->where('name', 'like', "%{$search}%");
        }
        return $query;
    });
```

---

## InputMask Client-Side Validation

InputMask provides client-side input formatting and validation.

### Implementation Approach

Filament supports adding input masks via the `mask()` method:

```php
use Filament\Forms\Components\TextInput;

TextInput::make('phone')
    ->label('Phone Number')
    ->mask('(999) 999-9999')
    ->placeholder('(555) 123-4567');
```

### Common Mask Patterns

#### 1. Numeric Formatting

```php
// Currency
TextInput::make('price')
    ->label('Price')
    ->prefix('$')
    ->numeric()
    ->mask(fn (TextInput\Mask $mask) => $mask
        ->numeric()
        ->decimalPlaces(2)
        ->decimalSeparator('.')
        ->thousandsSeparator(',')
    );

// Percentage
TextInput::make('discount')
    ->label('Discount')
    ->suffix('%')
    ->numeric()
    ->mask(fn (TextInput\Mask $mask) => $mask
        ->numeric()
        ->minValue(0)
        ->maxValue(100)
    );
```

#### 2. Date and Time

```php
// Date
TextInput::make('birth_date')
    ->label('Birth Date')
    ->mask('99/99/9999')
    ->placeholder('MM/DD/YYYY');

// Time
TextInput::make('appointment_time')
    ->label('Time')
    ->mask('99:99')
    ->placeholder('HH:MM');
```

#### 3. Phone and Communications

```php
// US Phone Number
TextInput::make('phone_us')
    ->label('Phone (US)')
    ->mask('(999) 999-9999')
    ->placeholder('(555) 123-4567');

// International Phone Number
TextInput::make('phone_intl')
    ->label('Phone (International)')
    ->mask('+99 (999) 999-9999')
    ->placeholder('+86 (138) 0013-8000');

// Email - using native HTML5 validation
TextInput::make('email')
    ->label('Email')
    ->email()
    ->placeholder('user@example.com');
```

#### 4. Network Addresses

```php
// IP Address
TextInput::make('ip_address')
    ->label('IP Address')
    ->mask('999.999.999.999')
    ->placeholder('192.168.1.1');

// MAC Address
TextInput::make('mac_address')
    ->label('MAC Address')
    ->mask('**:**:**:**:**:**')
    ->placeholder('00:1A:2B:3C:4D:5E');

// URL - using native HTML5 validation
TextInput::make('website')
    ->label('Website')
    ->url()
    ->placeholder('https://example.com');
```

#### 5. Credit Cards and Financial

```php
// Credit Card Number
TextInput::make('credit_card')
    ->label('Credit Card')
    ->mask('9999 9999 9999 9999')
    ->placeholder('1234 5678 9012 3456');

// CVV
TextInput::make('cvv')
    ->label('CVV')
    ->mask('999')
    ->placeholder('123');

// IBAN
TextInput::make('iban')
    ->label('IBAN')
    ->mask('AA99 9999 9999 9999 9999 9999')
    ->placeholder('GB82 WEST 1234 5698 7654 32');
```

### Extending ComparisonFilter with InputMask

```php
// Extension example
ComparisonFilter::make('price')
    ->label('Price')
    ->gte()
    ->numeric()
    ->prefix('$')
    ->mask(fn (TextInput\Mask $mask) => $mask
        ->numeric()
        ->decimalPlaces(2)
        ->decimalSeparator('.')
        ->thousandsSeparator(',')
    );
```

### Custom Mask Patterns

```php
use Filament\Forms\Components\TextInput;

TextInput::make('custom_code')
    ->label('Custom Code')
    ->mask(fn (TextInput\Mask $mask) => $mask
        ->pattern([
            '9' => '[0-9]',      // Digit
            'A' => '[A-Z]',      // Uppercase letter
            'a' => '[a-z]',      // Lowercase letter
            '*' => '[A-Za-z0-9]', // Letter or digit
        ])
        ->blocks([
            'code' => [
                'mask' => 'AAA-999-aaa',
                'lazy' => false,
            ],
        ])
    );
```

---

## FindInSet Filter Implementation

`FindInSet` is used to query comma-separated string fields.

### Creating FindInSetFilter

```php
<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class FindInSetFilter extends Filter
{
    protected array $options = [];

    protected bool $multiple = false;

    public function options(array $options): static
    {
        $this->options = $options;
        $this->configureForm();

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        $this->configureForm();

        return $this;
    }

    protected function configureForm(): void
    {
        if (empty($this->options)) {
            return;
        }

        $label = $this->getLabel() ?? $this->getName();

        $this->form([
            Select::make($this->multiple ? 'values' : 'value')
                ->label($label)
                ->options($this->options)
                ->multiple($this->multiple)
                ->native(false)
                ->placeholder('Select...')
                ->columnSpanFull(),
        ]);

        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $column = $this->getName();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return $query;
                }

                // FIND_IN_SET for multiple values
                return $query->where(function ($query) use ($column, $values) {
                    foreach ($values as $value) {
                        $query->orWhereRaw("FIND_IN_SET(?, {$column})", [$value]);
                    }
                });
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            // FIND_IN_SET for single value
            return $query->whereRaw("FIND_IN_SET(?, {$column})", [$value]);
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->getLabel() ?? $this->getName();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return [];
                }

                $labels = array_map(fn ($value) => $this->options[$value] ?? $value, $values);

                return [
                    Indicator::make("{$label}: ".implode(', ', $labels))
                        ->removeField('values'),
                ];
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $valueLabel = $this->options[$value] ?? $value;

            return [
                Indicator::make("{$label}: {$valueLabel}")
                    ->removeField('value'),
            ];
        });
    }
}
```

### Usage Example

```php
use Cooper\FilamentDcatFilters\Filters\FindInSetFilter;

FindInSetFilter::make('tags')
    ->label('Tags')
    ->options([
        'php' => 'PHP',
        'javascript' => 'JavaScript',
        'python' => 'Python',
        'ruby' => 'Ruby',
        'java' => 'Java',
    ])
    ->multiple();
```

---

## HiddenFilter Usage Guide

`HiddenFilter` is used to pass filter conditions via URL parameters without displaying them in the interface.

### Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\HiddenFilter;

// Define in the Resource's table() method
HiddenFilter::make('status')
    ->default('published')
    ->eq();
```

### Using with URL Parameters

HiddenFilter primarily passes values via URL parameters:

```
# Equal filter
/admin/posts?tableFilters[status][value]=published

# Greater than or equal filter
/admin/posts?tableFilters[views][value]=1000

# Not equal filter
/admin/posts?tableFilters[category][value]=draft
```

### Supported Operators

```php
// Equal (=)
HiddenFilter::make('status')
    ->default('published')
    ->eq();

// Not equal (!=)
HiddenFilter::make('status')
    ->default('draft')
    ->ne();

// Greater than (>)
HiddenFilter::make('views')
    ->default(100)
    ->gt();

// Greater than or equal (>=)
HiddenFilter::make('views')
    ->default(100)
    ->gte();

// Less than (<)
HiddenFilter::make('price')
    ->default(1000)
    ->lt();

// Less than or equal (<=)
HiddenFilter::make('price')
    ->default(1000)
    ->lte();
```

### Use Cases

1. **Preset Filter Conditions**: Preset filters when linking from other pages to list pages
2. **Multi-tenant Systems**: Automatically filter data for the current tenant
3. **Permission Control**: Automatically filter visible data based on user permissions

```php
// Example: Multi-tenant automatic filtering
HiddenFilter::make('tenant_id')
    ->default(auth()->user()->tenant_id)
    ->eq();

// Example: Only show records created by the current user
HiddenFilter::make('user_id')
    ->default(auth()->id())
    ->eq();
```

---

## Summary

### Implemented Features
1. ✅ NotLike Filter - Exclude text matches
2. ✅ NotIn Filter - Exclude options
3. ✅ Hidden Filter - Hidden filters (URL parameters)
4. ✅ DateComponentFilter - Year/Month/Day independent filters

### Implemented via Filament Native Support
5. ✅ API Data Source - Using `getSearchResultsUsing()`
6. ✅ InputMask - Using `mask()` method

### Additional Features
7. ✅ FindInSet Filter - Comma-separated string queries

All features have been implemented or complete implementation solutions have been provided!
