# RegexFilter

Filter records using regular expression pattern matching.

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\RegexFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            // User can input their own pattern
            RegexFilter::make('phone'),

            // Or use a fixed pattern
            RegexFilter::make('email')
                ->pattern('^[a-z]+@example\.com$'),
        ]);
}
```

## Pattern Modes

### User Pattern Mode (Default)

Users can input their own regex pattern:

```php
RegexFilter::make('description')
    ->placeholder('Enter regex pattern...')
```

### Fixed Pattern Mode

Apply a predefined pattern with a toggle:

```php
RegexFilter::make('phone')
    ->pattern('^1[3-9][0-9]{9}$')
```

When a fixed pattern is set, the filter displays a toggle instead of a text input.

### Switching Between Modes

```php
// Start with fixed pattern, then allow user input
RegexFilter::make('phone')
    ->pattern('^test$')
    ->userPattern()  // Switch back to user input mode
```

## Case Sensitivity

### Case Insensitive

```php
RegexFilter::make('email')
    ->caseInsensitive()
```

### Case Sensitive (Default)

```php
RegexFilter::make('code')
    ->caseSensitive()
```

## Built-in Presets

### China Mobile Number

```php
RegexFilter::make('phone')
    ->chinaMobile()
```

Pattern: `^1[3-9][0-9]{9}$`

### Email Address

```php
RegexFilter::make('email')
    ->email()
```

Pattern: `^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$`

### URL

```php
RegexFilter::make('website')
    ->url()
```

Pattern: `^https?://[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}`

### IPv4 Address

```php
RegexFilter::make('ip_address')
    ->ipv4()
```

Pattern: `^([0-9]{1,3}\.){3}[0-9]{1,3}$`

## Custom Placeholder

```php
RegexFilter::make('code')
    ->placeholder('Enter pattern (e.g., ^ABC-[0-9]+$)')
```

## Complete Example

```php
RegexFilter::make('product_code')
    ->label('Product Code')
    ->pattern('^[A-Z]{3}-[0-9]{4}$')
    ->caseInsensitive()
    ->columnSpan(1),

RegexFilter::make('search_pattern')
    ->label('Advanced Search')
    ->placeholder('Enter regex...')
    ->caseInsensitive()
    ->columnSpan(2),
```

## Use Cases

- Phone number format validation
- Email domain filtering (e.g., only company emails)
- Product code patterns
- Custom ID formats
- Data quality filtering

## Database Compatibility

This filter uses the `REGEXP` operator which is supported by:
- MySQL 5.x+ (uses `REGEXP`)
- MariaDB 5.x+
- PostgreSQL (uses `~` or `~*` for case-insensitive)

Note: SQLite has limited regex support and may require extensions.
