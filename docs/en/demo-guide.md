# Filament Dcat Filters - Complete Demo Guide

This guide demonstrates all available filter types and their usage in the `cooper/filament-dcat-filters` package.

## Accessing the Demo

Visit `http://localhost:8000/admin/posts` to view the complete demonstration of all filters.

---

## Filter Types Overview

### 1️⃣ **ScopeFilter** - Tab-Style Quick Filtering

**Function**: Similar to Dcat Admin's Scope feature, provides tab-style quick filtering options.

**Demo Examples**:

#### Example 1: Status Quick Filter
```php
ScopeFilter::make('status_scope')
    ->label('Status Quick Filter')
    ->scopes([
        'all' => ['label' => 'All', 'default' => true],
        'published' => [
            'label' => 'Published',
            'query' => fn ($query) => $query->where('status', 'published'),
        ],
        'draft' => [
            'label' => 'Draft',
            'query' => fn ($query) => $query->where('status', 'draft'),
        ],
        'featured' => [
            'label' => 'Featured',
            'query' => fn ($query) => $query->where('is_featured', true),
        ],
    ])
    ->columns(4)
    ->columnSpan('full');
```

#### Example 2: Date Range Quick Filter
```php
ScopeFilter::forDates('created_at')
    ->label('Date Range Quick Filter')
    ->columnSpan('full');
```
Provides preset options: Today, Yesterday, Last 7 days, Last 30 days, This month, Last month.

#### Example 3: Combined Condition Filter
```php
ScopeFilter::make('quality_price')
    ->label('Quality & Price Combo')
    ->scopes([
        'all' => ['label' => 'All', 'default' => true],
        'premium' => [
            'label' => 'Premium (Rating≥4, Price≥100)',
            'query' => fn ($query) => $query
                ->where('rating', '>=', 4.0)
                ->where('price', '>=', 100),
        ],
        'budget' => [
            'label' => 'Budget (Rating≥3.5, Price<50)',
            'query' => fn ($query) => $query
                ->where('rating', '>=', 3.5)
                ->where('price', '<', 50),
        ],
    ])
    ->columns(4);
```

---

### 2️⃣ **LikeFilter** - Text Fuzzy Search

**Function**: Performs LIKE fuzzy search on string fields.

**Demo Examples**:

```php
LikeFilter::make('title')
    ->label('Title (Like)')
    ->columnSpan(2);

LikeFilter::make('content')
    ->label('Content (Like)')
    ->columnSpan(2);
```

**SQL Generated**: `WHERE title LIKE '%input value%'`

---

### 3️⃣ **InFilter** - Single/Multiple Selection Filter

**Function**: Single or multiple selection filtering from predefined options.

**Demo Examples**:

#### Example 1: Multiple Selection (default)
```php
InFilter::make('category')
    ->label('Category (In)')
    ->options([
        'Technology' => 'Technology',
        'Business' => 'Business',
        'Lifestyle' => 'Lifestyle',
        'Travel' => 'Travel',
        'Food' => 'Food',
        'Sports' => 'Sports',
    ])
    ->columnSpan(2);
```

#### Example 2: Single Selection
```php
InFilter::make('status')
    ->label('Status (In Single)')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ])
    ->columnSpan(2);
```

**SQL Generated**:
- Multiple: `WHERE category IN ('Technology', 'Business')`
- Single: `WHERE status = 'draft'`

---

### 4️⃣ **SelectTableFilter** - Related Model Selection

**Function**: Select records from related models (with search support).

**Demo Example**:

```php
SelectTableFilter::make('author_id')
    ->label('Author (SelectTable)')
    ->model(User::class)
    ->multiple()
    ->columnSpan(2);
```

**Features**:
- Supports single and multiple selection
- Supports search
- Automatically displays the name field of related models

---

### 5️⃣ **ModalSelectFilter** - Dcat Admin Style Modal Selection

**Function**: Dcat Admin style modal dialog table selector, displays a complete table in a popup for users to browse and select.

**Demo Examples**:

#### Example 1: Basic Usage
```php
ModalSelectFilter::make('author_id')
    ->label('Author (Modal Select)')
    ->model(User::class, 'name', 'id')
    ->dialogTitle('Select Author')
    ->columnSpan(2);
```

#### Example 2: Multiple Selection + Custom Display Columns
```php
ModalSelectFilter::make('category_ids')
    ->label('Category (Modal Multi-Select)')
    ->model(Category::class, 'name', 'id')
    ->multiple()
    ->dialogTitle('Select Categories')
    ->dialogWidth('1000px')
    ->displayColumns([
        'id' => 'ID',
        'name' => 'Name',
        'description' => 'Description',
    ])
    ->searchable(['name', 'description'])
    ->columnSpan(2);
```

#### Example 3: Using Relationships
```php
ModalSelectFilter::make('user_id')
    ->label('User (Modal Relationship)')
    ->relationship('user', 'name', 'id')
    ->dialogTitle('Select User')
    ->dialogWidth('900px')
    ->columnSpan(2);
```

**Features**:
- Modal dialog displays complete table
- Supports single and multiple selection
- Configurable display and search columns
- Supports pagination and sorting
- Customizable dialog width and title
- Suitable for scenarios requiring viewing multiple columns before selection

**Difference from SelectTableFilter**:
- **SelectTableFilter**: Dropdown selection, compact and concise
- **ModalSelectFilter**: Modal dialog table, suitable for complex selection

---

### 6️⃣ **RangeFilter** - Range Filtering

**Function**: Range filtering for numeric, date, and time values (From - To).

**Demo Examples**:

#### Example 1: Integer Range
```php
RangeFilter::make('views')
    ->label('Views Range')
    ->integer()
    ->placeholders('Min', 'Max')
    ->columnSpan(2);
```

#### Example 2: Numeric Range
```php
RangeFilter::make('price')
    ->label('Price Range')
    ->numeric()
    ->placeholders('Min', 'Max')
    ->columnSpan(2);

RangeFilter::make('rating')
    ->label('Rating Range')
    ->numeric()
    ->placeholders('Min', 'Max')
    ->columnSpan(2);
```

#### Example 3: DateTime Range
```php
RangeFilter::make('published_at')
    ->label('Published Date Range')
    ->datetime()
    ->placeholders('From', 'To')
    ->columnSpan(2);
```

**SQL Generated**:
- Only From filled: `WHERE views >= 100`
- Only To filled: `WHERE views <= 1000`
- Both filled: `WHERE views BETWEEN 100 AND 1000`

**Supported Types**:
- `->integer()` - Integer
- `->numeric()` - Numeric (supports decimals)
- `->date()` - Date
- `->datetime()` - DateTime
- `->time()` - Time

---

### 7️⃣ **ComparisonFilter** - Comparison Filtering

**Function**: Single comparison operations on fields (=, >, >=, <, <=, !=).

**Demo Examples**:

#### Example 1: Greater Than or Equal
```php
ComparisonFilter::make('views')
    ->label('Views Min (>=)')
    ->gte()
    ->integer()
    ->columnSpan(2);
```

#### Example 2: Less Than or Equal
```php
ComparisonFilter::make('price')
    ->label('Price Max (<=)')
    ->lte()
    ->numeric()
    ->columnSpan(2);
```

#### Example 3: Equal
```php
ComparisonFilter::make('rating')
    ->label('Rating Exact (=)')
    ->eq()
    ->numeric()
    ->columnSpan(2);
```

**Available Methods**:
- `->eq()` - Equal (=)
- `->gt()` - Greater than (>)
- `->gte()` - Greater than or equal (>=)
- `->lt()` - Less than (<)
- `->lte()` - Less than or equal (<=)
- `->ne()` - Not equal (!=)

---

### 8️⃣ **BetweenFilter** - Simplified Between Filter

**Function**: Simplified version of Between filter, automatically generates From and To input boxes.

**Demo Examples**:

```php
BetweenFilter::make('price')
    ->label('Price Between')
    ->numeric()
    ->columnSpan(2);

BetweenFilter::make('rating')
    ->label('Rating Between')
    ->numeric()
    ->columnSpan(2);
```

**Difference from RangeFilter**:
- BetweenFilter: More concise implementation, handles automatically
- RangeFilter: More flexible, supports more configuration options

---

### 9️⃣ **TernaryFilter** - Filament Native Ternary Filter

**Function**: Filament's native ternary filter (True/False/All).

**Demo Example**:

```php
TernaryFilter::make('is_featured')
    ->label('Featured (Ternary)')
    ->placeholder('All')
    ->columnSpan(2);
```

---

## Layout Configuration

### Filter Position

```php
->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
```

**Available Options**:
- `AboveContent` - Filters displayed above the table (recommended)
- `Dropdown` - Filters displayed in a dropdown panel

### Column Layout

```php
->filtersFormColumns(4)  // 4-column layout
```

**columnSpan Control**:
- `->columnSpan(1)` - Occupies 1 column
- `->columnSpan(2)` - Occupies 2 columns
- `->columnSpan('full')` - Occupies full row

### Filter Mode

```php
->deferFilters()  // Deferred filtering, shows "Search" and "Reset" buttons
// or
->deferFilters(false)  // Real-time filtering, takes effect on input
```

---

## Usage Recommendations

### 1. **Horizontal Compact Layout** (similar to Dcat Admin)

```php
->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
->filtersFormColumns(8)
->deferFilters()
```

Each filter uses `->columnSpan(1)` or `->columnSpan(2)`.

### 2. **Standard Layout** (recommended for complex filtering)

```php
->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
->filtersFormColumns(4)
->deferFilters()
```

### 3. **Real-time Filter Layout** (suitable for few filters)

```php
->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
->filtersFormColumns(3)
->deferFilters(false)
```

---

## Complete Example Code

See `app/Filament/Resources/Posts/Tables/PostsTable.php` for the complete configuration of all filters.

---

## Test Data

Run the following command to generate test data:

```bash
php artisan app:generate-test-data
```

This will generate 100 test records with various scenarios.

---

## More Documentation

- [README.md](README.md) - Installation and quick start
- [docs/filters.md](docs/filters.md) - Detailed filter documentation
- [docs/examples.md](docs/examples.md) - More usage examples

---

## Feedback & Support

For questions or suggestions, please visit the project repository to submit an Issue.
