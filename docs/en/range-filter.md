# Range Filter

The Range Filter provides simplified date/number range filtering with automatic handling of single-sided ranges. It reduces the amount of code needed compared to creating custom filters.

## Basic Usage

### Date Range

```php
use Cooper\FilamentDcatFilters\Filters\RangeFilter;

RangeFilter::make('created_at')
    ->label('Created Date')
    ->date();
```

### DateTime Range

```php
RangeFilter::make('created_at')
    ->label('Created Time')
    ->datetime();
```

### Numeric Range

```php
RangeFilter::make('price')
    ->label('Price')
    ->numeric();
```

### Integer Range

```php
RangeFilter::make('views')
    ->label('Views')
    ->integer();
```

## Configuration Options

### Custom Date Format

```php
RangeFilter::make('created_at')
    ->datetime('Y-m-d H:i')
    ->format('Y-m-d H:i');
```

### Custom Placeholders

```php
// Using named parameters
RangeFilter::make('price')
    ->numeric()
    ->placeholders('Minimum Price', 'Maximum Price');

// Using array
RangeFilter::make('price')
    ->numeric()
    ->placeholders(['from' => 'Min', 'to' => 'Max']);
```

### Convert to Timestamp

Useful for datetime columns stored as integers:

```php
RangeFilter::make('created_at')
    ->datetime()
    ->toTimestamp();
```

## Filter Types

### Date Filters

#### Simple Date

```php
RangeFilter::make('birth_date')
    ->label('Birth Date')
    ->date();
```

#### DateTime with Seconds

```php
RangeFilter::make('published_at')
    ->label('Published At')
    ->datetime('Y-m-d H:i:s');
```

#### Time Only

```php
RangeFilter::make('opening_time')
    ->label('Opening Time')
    ->time();
```

### Number Filters

#### Decimal Numbers

```php
RangeFilter::make('price')
    ->label('Price Range')
    ->numeric();
```

#### Integers Only

```php
RangeFilter::make('quantity')
    ->label('Quantity')
    ->integer();
```

## Automatic Range Handling

The RangeFilter automatically handles three scenarios:

1. **Both values provided**: Uses `whereBetween($column, [$from, $to])`
2. **Only 'from' value**: Uses `where($column, '>=', $from)`
3. **Only 'to' value**: Uses `where($column, '<=', $to)`

Example:
```php
// User enters only "from" value: 100
// Generated query: WHERE views >= 100

// User enters only "to" value: 1000
// Generated query: WHERE views <= 1000

// User enters both: 100 and 1000
// Generated query: WHERE views BETWEEN 100 AND 1000
```

## Advanced Examples

### Age Range

```php
RangeFilter::make('age')
    ->label('Age')
    ->integer()
    ->placeholders('Minimum Age', 'Maximum Age');
```

### Price Range with Currency

```php
RangeFilter::make('price')
    ->label('Price (USD)')
    ->numeric()
    ->placeholders('$0', '$10,000');
```

### Date Range with Custom Format

```php
RangeFilter::make('event_date')
    ->label('Event Date')
    ->date()
    ->format('F j, Y') // January 1, 2025
    ->placeholders('Start Date', 'End Date');
```

### Timestamp-based Filtering

```php
// For columns storing unix timestamps
RangeFilter::make('last_login')
    ->label('Last Login')
    ->datetime()
    ->toTimestamp();
```

## Configuration File

Default values can be set in `config/filament-dcat-filters.php`:

```php
'range' => [
    'date_format' => 'Y-m-d',
    'datetime_format' => 'Y-m-d H:i:s',
    'time_format' => 'H:i:s',
    'placeholders' => [
        'from' => 'From',
        'to' => 'To',
    ],
],
```

## Filter Indicators

The Range Filter automatically shows indicators when active:

```
Created Date from 2025-01-01
Created Date to 2025-12-31
```

Or when both values are provided:

```
Created Date from 2025-01-01
Created Date to 2025-12-31
```

## Tips

1. **Single-sided Ranges**: Users can leave one field empty for open-ended ranges
2. **Date Pickers**: Uses Filament's native date pickers with calendar UI
3. **Validation**: Input validation is handled automatically by Filament Forms
4. **Responsive**: Works great on mobile devices
5. **Clear Values**: Users can clear individual fields or the entire filter

## Comparison with Dcat Admin

### Dcat Admin
```php
$filter->between('created_at')->datetime();
```

### Filament Dcat Filters
```php
RangeFilter::make('created_at')->datetime();
```

The API is nearly identical! The Filament version integrates seamlessly with Filament's form components and provides better UI/UX out of the box.

## Common Patterns

### Sales Report Date Range

```php
RangeFilter::make('order_date')
    ->label('Order Date')
    ->date()
    ->placeholders('From Date', 'To Date');
```

### Product Price Filtering

```php
RangeFilter::make('price')
    ->label('Price Range')
    ->numeric()
    ->placeholders('Min Price', 'Max Price');
```

### Analytics Views Range

```php
RangeFilter::make('views')
    ->label('View Count')
    ->integer()
    ->placeholders('Minimum', 'Maximum');
```
