# GeoLocationFilter

Filter records by geographic proximity using latitude and longitude coordinates.

## Basic Usage

```php
use Cooper\FilamentDcatFilters\Filters\GeoLocationFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            GeoLocationFilter::make('location')
                ->latitude('lat')
                ->longitude('lng')
                ->radius(10, 'km'),
        ]);
}
```

## Column Configuration

### Default Columns

By default, the filter uses `latitude` and `longitude` columns.

### Custom Column Names

```php
GeoLocationFilter::make('location')
    ->latitude('lat')
    ->longitude('lng')
```

### Set Both at Once

```php
GeoLocationFilter::make('location')
    ->coordinates('store_lat', 'store_lng')
```

## Radius Configuration

### Default Radius

```php
GeoLocationFilter::make('location')
    ->radius(10)  // 10 kilometers by default
```

### With Unit

```php
GeoLocationFilter::make('location')
    ->radius(5, 'mi')  // 5 miles
```

## Distance Units

### Kilometers (Default)

```php
GeoLocationFilter::make('location')
    ->kilometers()
```

### Miles

```php
GeoLocationFilter::make('location')
    ->miles()
```

### Meters

```php
GeoLocationFilter::make('location')
    ->meters()
```

## Center Point

Set a default center point for the search:

```php
GeoLocationFilter::make('location')
    ->center(40.7128, -74.0060)  // New York City
    ->radius(25, 'km')
```

## Complete Example

```php
GeoLocationFilter::make('store_location')
    ->label('Near Location')
    ->coordinates('store_lat', 'store_lng')
    ->radius(10, 'mi')
    ->center(34.0522, -118.2437)  // Los Angeles
    ->columnSpan(2),
```

## Form Fields

The filter displays three input fields:
- **Latitude**: Decimal degrees (e.g., 40.7128)
- **Longitude**: Decimal degrees (e.g., -74.0060)
- **Radius**: Distance in the configured unit

## How It Works

The filter uses the **Haversine formula** to calculate the great-circle distance between two points on Earth:

```
a = sin²(Δlat/2) + cos(lat1) × cos(lat2) × sin²(Δlng/2)
c = 2 × atan2(√a, √(1-a))
d = R × c
```

Where:
- `R` = Earth's radius (6,371 km)
- `lat1`, `lng1` = Center point coordinates
- `lat2`, `lng2` = Record coordinates
- `d` = Distance in kilometers

## Use Cases

- Store locator (find stores within X miles)
- Delivery zone filtering
- Event search by location
- Property search within an area
- Service provider availability

## Database Requirements

Your table must have:
- A latitude column (decimal, typically -90 to 90)
- A longitude column (decimal, typically -180 to 180)

Example migration:

```php
Schema::create('stores', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->decimal('latitude', 10, 8);
    $table->decimal('longitude', 11, 8);
    $table->timestamps();
});
```

## Performance Tips

For better performance with large datasets:

1. **Add spatial index** (MySQL 8.0+):
   ```sql
   ALTER TABLE stores ADD SPATIAL INDEX(location);
   ```

2. **Use bounding box pre-filter**:
   The Haversine calculation is CPU-intensive. Consider adding a rough bounding box filter first.

3. **Cache results** for common search areas.

## Database Compatibility

The Haversine formula SQL is compatible with:
- MySQL 5.7+
- MariaDB 10.2+
- PostgreSQL 9.0+
- SQLite (with math functions enabled)

For advanced geospatial queries, consider using:
- MySQL's built-in spatial functions (`ST_Distance_Sphere`)
- PostgreSQL's PostGIS extension
