# InputMaskFilter

Filter with formatted input using input masks for consistent data entry.

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\InputMaskFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            InputMaskFilter::make('phone')
                ->mask('(999) 999-9999'),
        ]);
}
```

## Mask Characters

| Character | Description |
|-----------|-------------|
| `9` | Numeric (0-9) |
| `a` | Alphabetical (a-z, A-Z) |
| `*` | Alphanumeric |

## Custom Mask

```php
InputMaskFilter::make('product_code')
    ->mask('AAA-9999')
    ->placeholder('ABC-1234')
```

## Built-in Presets

### Phone Number

```php
InputMaskFilter::make('phone')
    ->phone()  // (999) 999-9999

// Custom format
InputMaskFilter::make('phone')
    ->phone('999-999-9999')
```

### China Phone Number

```php
InputMaskFilter::make('mobile')
    ->chinaPhone()  // 999 9999 9999
```

### Credit Card

```php
InputMaskFilter::make('card_number')
    ->creditCard()  // 9999 9999 9999 9999
```

### Date

```php
InputMaskFilter::make('birth_date')
    ->date()  // 9999-99-99

// Custom format
InputMaskFilter::make('birth_date')
    ->date('99/99/9999')
```

### Time

```php
InputMaskFilter::make('start_time')
    ->time()  // 99:99

// With seconds
InputMaskFilter::make('start_time')
    ->time('99:99:99')
```

### IP Address

```php
InputMaskFilter::make('ip_address')
    ->ip()  // 999.999.999.999
```

### ZIP Code

```php
InputMaskFilter::make('postal_code')
    ->zipCode()  // 99999

// Extended format
InputMaskFilter::make('postal_code')
    ->zipCode('99999-9999')
```

### Currency

```php
InputMaskFilter::make('amount')
    ->currency()  // $0.00

// Custom prefix
InputMaskFilter::make('amount')
    ->currency('¥')
```

## Comparison Operators

### Like Match (Default)

Searches for values containing the input:

```php
InputMaskFilter::make('phone')
    ->phone()
    ->like()
```

### Exact Match

Requires exact match:

```php
InputMaskFilter::make('phone')
    ->phone()
    ->exact()
```

## Strip Mask Characters

By default, mask characters are stripped before querying the database.

### Enable Strip (Default)

```php
InputMaskFilter::make('phone')
    ->phone()
    ->stripMask()  // (555) 123-4567 → 5551234567
```

### Disable Strip

Keep mask characters in the query:

```php
InputMaskFilter::make('formatted_code')
    ->mask('AAA-999')
    ->stripMask(false)
```

### Custom Strip Pattern

```php
InputMaskFilter::make('phone')
    ->mask('(999) 999-9999')
    ->stripPattern('/[^0-9]/')  // Keep only digits
```

## Custom Placeholder

```php
InputMaskFilter::make('phone')
    ->phone()
    ->placeholder('Enter phone number')
```

## Complete Example

```php
InputMaskFilter::make('customer_phone')
    ->label('Customer Phone')
    ->phone()
    ->exact()
    ->stripPattern('/[^0-9]/')
    ->columnSpan(1),

InputMaskFilter::make('order_code')
    ->label('Order Code')
    ->mask('ORD-9999-AAA')
    ->placeholder('ORD-1234-ABC')
    ->stripMask(false)
    ->exact()
    ->columnSpan(1),
```

## Use Cases

- Phone number search with consistent formatting
- Credit card lookup (last 4 digits)
- Date-based filtering with format validation
- Product code search
- IP address filtering

## Notes

- The input mask uses Alpine.js mask plugin format
- Mask characters help users enter data in the correct format
- Strip mask feature ensures database queries work regardless of stored format
