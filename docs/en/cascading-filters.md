# Cascading Select Filters

Cascading select filters allow you to create dependent dropdowns where the options in child dropdowns depend on the selection in parent dropdowns.

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\CascadingSelectFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            CascadingSelectFilter::make('location')
                ->addLevel(
                    name: 'country_id',
                    label: 'Country',
                    model: Country::class
                )
                ->addLevel(
                    name: 'state_id',
                    label: 'State',
                    model: State::class,
                    parentField: 'country_id',
                    foreignKey: 'country_id'
                )
                ->addLevel(
                    name: 'city_id',
                    label: 'City',
                    model: City::class,
                    parentField: 'state_id',
                    foreignKey: 'state_id'
                ),
        ]);
}
```

## Using the Levels Method

For cleaner code, you can use the `levels()` method with an array:

```php
CascadingSelectFilter::make('location')
    ->levels([
        [
            'name' => 'country_id',
            'label' => 'Country',
            'model' => Country::class,
        ],
        [
            'name' => 'state_id',
            'label' => 'State',
            'model' => State::class,
            'parentField' => 'country_id',
            'foreignKey' => 'country_id',
        ],
        [
            'name' => 'city_id',
            'label' => 'City',
            'model' => City::class,
            'parentField' => 'state_id',
            'foreignKey' => 'state_id',
        ],
    ]);
```

## Level Configuration Options

Each level accepts the following configuration:

| Option | Type | Description | Default |
|--------|------|-------------|---------|
| `name` | string | Field/column name | Required |
| `label` | string | Display label | Required |
| `model` | string | Eloquent model class | Required |
| `parentField` | string\|null | Parent field name | `null` |
| `foreignKey` | string | Foreign key column | `'parent_id'` |
| `titleColumn` | string | Column for option labels | `'name'` |
| `keyColumn` | string | Column for option values | `'id'` |

## Preset Filters

### Location Filter

Quickly create a Country → State → City cascade:

```php
CascadingSelectFilter::forLocation(
    countryModel: Country::class,
    stateModel: State::class,
    cityModel: City::class,
    countryLabel: 'Country',  // Optional
    stateLabel: 'State',      // Optional
    cityLabel: 'City'         // Optional
);
```

### Category Filter

Create a nested category filter:

```php
CascadingSelectFilter::forCategory(
    model: Category::class,
    depth: 3,                   // Number of levels
    rootLabel: 'Category',      // Optional
    childLabel: 'Subcategory',  // Optional
    parentColumn: 'parent_id'   // Optional
);
```

## How It Works

1. **Root Level**: The first level loads all available options from the model
2. **Child Levels**: When a parent is selected, child options are filtered by the foreign key
3. **Cascading Clear**: When a parent changes, all child fields are automatically cleared
4. **Query Building**: Selected values are applied to the query using `where()` clauses

## Custom Title and Key Columns

Use different columns for display and value:

```php
CascadingSelectFilter::make('product')
    ->addLevel(
        name: 'brand_id',
        label: 'Brand',
        model: Brand::class,
        titleColumn: 'brand_name',  // Display this column
        keyColumn: 'id'             // Use this as value
    )
    ->addLevel(
        name: 'category_id',
        label: 'Category',
        model: ProductCategory::class,
        parentField: 'brand_id',
        foreignKey: 'brand_id',
        titleColumn: 'category_title',
        keyColumn: 'category_id'
    );
```

## Filter Indicators

Each selected level shows as a separate indicator that can be individually removed:

```
Country: United States × | State: California × | City: Los Angeles ×
```

## Database Requirements

Your database should have the appropriate foreign key relationships:

```php
// countries table
Schema::create('countries', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

// states table
Schema::create('states', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('country_id')->constrained();
    $table->timestamps();
});

// cities table
Schema::create('cities', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('state_id')->constrained();
    $table->timestamps();
});
```

## Self-Referencing Categories

For self-referencing models (like nested categories):

```php
// categories table
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('parent_id')->nullable()->constrained('categories');
    $table->timestamps();
});

// Filter
CascadingSelectFilter::make('category')
    ->addLevel(
        name: 'category_id',
        label: 'Category',
        model: Category::class,
        titleColumn: 'name'
    )
    ->addLevel(
        name: 'subcategory_id',
        label: 'Subcategory',
        model: Category::class,
        parentField: 'category_id',
        foreignKey: 'parent_id',
        titleColumn: 'name'
    );
```

## Complete Example

```php
use Cooper\FilamentDcatFilters\Filters\CascadingSelectFilter;
use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;

public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('country.name'),
            TextColumn::make('state.name'),
            TextColumn::make('city.name'),
        ])
        ->filters([
            CascadingSelectFilter::make('location')
                ->label('Location')
                ->levels([
                    [
                        'name' => 'country_id',
                        'label' => 'Country',
                        'model' => Country::class,
                    ],
                    [
                        'name' => 'state_id',
                        'label' => 'State',
                        'model' => State::class,
                        'parentField' => 'country_id',
                        'foreignKey' => 'country_id',
                    ],
                    [
                        'name' => 'city_id',
                        'label' => 'City',
                        'model' => City::class,
                        'parentField' => 'state_id',
                        'foreignKey' => 'state_id',
                    ],
                ]),
        ])
        ->headerActions([
            ResetFiltersAction::make(),
        ]);
}
```
