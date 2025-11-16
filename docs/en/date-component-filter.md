# Date Component Filter

The Date Component Filter allows you to filter records by specific date components (year, month, or day) extracted from datetime columns. This is useful when you want to filter by a specific month across all years, or a specific day of the month.

## Features

- **Year Filtering** - Filter by specific year (e.g., all records from 2024)
- **Month Filtering** - Filter by month number (e.g., all January records)
- **Day Filtering** - Filter by day of month (e.g., all records from the 15th)
- **SQL Function-Based** - Uses database SQL functions (YEAR, MONTH, DAY)
- **Automatic Options** - Generates dropdown options automatically
- **Localized Labels** - Supports translation keys for month names

## Basic Usage

### Filter by Year

```php
use Cooper\FilamentDcatFilters\Filters\DateComponentFilter;

DateComponentFilter::make('created_at')
    ->label('Year')
    ->year();
```

This generates a dropdown with the current year and 10 previous years (e.g., 2024, 2023, 2022, ..., 2014).

### Filter by Month

```php
DateComponentFilter::make('created_at')
    ->label('Month')
    ->month();
```

This generates a dropdown with all 12 months, using localized month names.

### Filter by Day

```php
DateComponentFilter::make('created_at')
    ->label('Day')
    ->day();
```

This generates a dropdown with days 01-31.

## Real-World Examples

### Birthday Filter (Month and Day)

```php
use Cooper\FilamentDcatFilters\Filters\DateComponentFilter;

public static function table(Table $table): Table
{
    return $table
        ->filters([
            DateComponentFilter::make('birth_date')
                ->label('Birth Month')
                ->month(),

            DateComponentFilter::make('birth_date')
                ->label('Birth Day')
                ->day(),
        ]);
}
```

### Financial Year Filter

```php
DateComponentFilter::make('transaction_date')
    ->label('Transaction Year')
    ->year();
```

### Seasonal Analysis

```php
// Filter by quarter
DateComponentFilter::make('order_date')
    ->label('Order Month')
    ->month();
```

## How It Works

### SQL Query Generation

The filter uses database-specific SQL functions to extract date components:

**Year Filter:**
```sql
WHERE YEAR(created_at) = ?
```

**Month Filter:**
```sql
WHERE MONTH(created_at) = ?
```

**Day Filter:**
```sql
WHERE DAY(created_at) = ?
```

### Year Range

By default, the year filter shows the current year plus 10 previous years. This is configured in the source code:

```php
$currentYear = (int) date('Y');
$years = [];
for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
    $years[$i] = (string) $i;
}
```

## Configuration

### Custom Labels

```php
DateComponentFilter::make('created_at')
    ->label('Creation Year')  // Custom label
    ->year();
```

### Column Span

```php
DateComponentFilter::make('created_at')
    ->year()
    ->columnSpan(2);  // Takes 2 columns in filter grid
```

## Localization

Month names are automatically localized using translation keys:

**Translation File:** `resources/lang/en/filament-dcat-filters.php`

```php
'months' => [
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    // ... etc
],

'date_component' => [
    'year' => 'Year',
    'month' => 'Month',
    'day' => 'Day',
    'select_year' => 'Select Year',
    'select_month' => 'Select Month',
    'select_day' => 'Select Day',
],
```

## Database Compatibility

This filter uses standard SQL functions that work with:

- ✅ MySQL / MariaDB
- ✅ PostgreSQL
- ✅ SQLite
- ✅ SQL Server

## Comparison with Other Filters

### vs RangeFilter

**DateComponentFilter:**
- Filters by component (year, month, day)
- Uses dropdown selection
- Exact match only

**RangeFilter:**
- Filters by date range (from-to)
- Uses date pickers
- Range queries (BETWEEN)

### vs ScopeFilter

**DateComponentFilter:**
- Dynamic dropdown options
- Single component extraction
- Database-driven

**ScopeFilter:**
- Predefined scopes
- Multiple conditions per scope
- Application-driven

## Use Cases

### E-commerce

```php
// Filter orders by month for seasonal analysis
DateComponentFilter::make('order_date')
    ->label('Order Month')
    ->month();
```

### HR / Employee Management

```php
// Filter employees by hire year
DateComponentFilter::make('hire_date')
    ->label('Hire Year')
    ->year();

// Birthday reminders
DateComponentFilter::make('birth_date')
    ->label('Birth Month')
    ->month();
```

### Content Management

```php
// Filter posts by publication year
DateComponentFilter::make('published_at')
    ->label('Published Year')
    ->year();
```

### Financial Reporting

```php
// Filter transactions by fiscal year
DateComponentFilter::make('transaction_date')
    ->label('Fiscal Year')
    ->year();

// Monthly expense reports
DateComponentFilter::make('expense_date')
    ->label('Expense Month')
    ->month();
```

## Advanced Usage

### Multiple Component Filters

You can combine multiple DateComponentFilter instances for precise filtering:

```php
public static function table(Table $table): Table
{
    return $table
        ->filters([
            DateComponentFilter::make('event_date')
                ->label('Event Year')
                ->year(),

            DateComponentFilter::make('event_date')
                ->label('Event Month')
                ->month(),

            DateComponentFilter::make('event_date')
                ->label('Event Day')
                ->day(),
        ])
        ->filtersFormColumns(3);  // Display filters in 3 columns
}
```

This allows users to filter by "All events on January 15th, 2024" by selecting:
- Year: 2024
- Month: 01 (January)
- Day: 15

## Filter Indicator

When a date component filter is active, it displays an indicator showing the selected value:

```
Year: 2024
```

Users can click the indicator to remove the filter.

## Performance Considerations

### Indexing

For optimal performance, consider adding functional indexes on your date columns:

**MySQL:**
```sql
ALTER TABLE posts
ADD INDEX idx_created_year ((YEAR(created_at)));

ALTER TABLE posts
ADD INDEX idx_created_month ((MONTH(created_at)));
```

**PostgreSQL:**
```sql
CREATE INDEX idx_created_year ON posts (EXTRACT(YEAR FROM created_at));
CREATE INDEX idx_created_month ON posts (EXTRACT(MONTH FROM created_at));
```

### Query Performance

The filter uses `whereRaw()` which prevents index usage on the column itself. For large tables with millions of records, consider:

1. Adding functional indexes (as shown above)
2. Using materialized views with pre-calculated year/month/day columns
3. Denormalizing data by storing year/month/day in separate columns

## Troubleshooting

### Month Names Not Translated

**Problem:** Month dropdown shows keys like "01", "02" instead of "January", "February"

**Solution:** Ensure translation files are published:

```bash
php artisan vendor:publish --tag=filament-dcat-filters-translations
```

### SQL Function Error

**Problem:** Database error "Unknown function YEAR()"

**Solution:** This filter requires MySQL/PostgreSQL/SQLite. For SQLite, ensure you're using SQLite 3.x. For other databases, you may need to adjust the SQL function syntax.

## Summary

DateComponentFilter provides a simple, dropdown-based way to filter by year, month, or day components of datetime columns. It's ideal for:

- Seasonal analysis
- Birthday/anniversary filtering
- Fiscal year reporting
- Publication date filtering
- Any scenario requiring filtering by date components rather than date ranges
