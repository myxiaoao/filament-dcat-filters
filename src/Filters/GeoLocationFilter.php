<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class GeoLocationFilter extends Filter
{
    protected string $latitudeColumn = 'latitude';

    protected string $longitudeColumn = 'longitude';

    protected float $defaultRadius = 10;

    protected string $unit = 'km';

    protected ?float $centerLatitude = null;

    protected ?float $centerLongitude = null;

    /**
     * Radius conversion factors to kilometers.
     */
    protected array $unitFactors = [
        'km' => 1,
        'mi' => 1.60934,
        'm' => 0.001,
    ];

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(2);
        $this->configureForm();
    }

    /**
     * Set the latitude column name.
     */
    public function latitude(string $column): static
    {
        $this->latitudeColumn = $column;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the longitude column name.
     */
    public function longitude(string $column): static
    {
        $this->longitudeColumn = $column;
        $this->configureForm();

        return $this;
    }

    /**
     * Set both latitude and longitude columns.
     */
    public function coordinates(string $latitudeColumn, string $longitudeColumn): static
    {
        $this->latitudeColumn = $latitudeColumn;
        $this->longitudeColumn = $longitudeColumn;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the default radius and unit.
     */
    public function radius(float $radius, string $unit = 'km'): static
    {
        $this->defaultRadius = $radius;
        $this->unit = strtolower($unit);
        $this->configureForm();

        return $this;
    }

    /**
     * Set the center point for the search.
     */
    public function center(float $latitude, float $longitude): static
    {
        $this->centerLatitude = $latitude;
        $this->centerLongitude = $longitude;
        $this->configureForm();

        return $this;
    }

    /**
     * Use kilometers as the unit.
     */
    public function kilometers(): static
    {
        $this->unit = 'km';
        $this->configureForm();

        return $this;
    }

    /**
     * Use miles as the unit.
     */
    public function miles(): static
    {
        $this->unit = 'mi';
        $this->configureForm();

        return $this;
    }

    /**
     * Use meters as the unit.
     */
    public function meters(): static
    {
        $this->unit = 'm';
        $this->configureForm();

        return $this;
    }

    /**
     * Get the unit label for display.
     */
    protected function getUnitLabel(): string
    {
        return match ($this->unit) {
            'km' => __('filament-dcat-filters::filament-dcat-filters.geo.km'),
            'mi' => __('filament-dcat-filters::filament-dcat-filters.geo.mi'),
            'm' => __('filament-dcat-filters::filament-dcat-filters.geo.m'),
            default => $this->unit,
        };
    }

    /**
     * Configure form component.
     */
    protected function configureForm(): void
    {
        $label = $this->getLabel() ?? __('filament-dcat-filters::filament-dcat-filters.geo.location');

        $this->form([
            Grid::make(3)
                ->schema([
                    TextInput::make('latitude')
                        ->label(__('filament-dcat-filters::filament-dcat-filters.geo.latitude'))
                        ->placeholder(__('filament-dcat-filters::filament-dcat-filters.geo.latitude_placeholder'))
                        ->numeric()
                        ->default($this->centerLatitude),
                    TextInput::make('longitude')
                        ->label(__('filament-dcat-filters::filament-dcat-filters.geo.longitude'))
                        ->placeholder(__('filament-dcat-filters::filament-dcat-filters.geo.longitude_placeholder'))
                        ->numeric()
                        ->default($this->centerLongitude),
                    TextInput::make('radius')
                        ->label(__('filament-dcat-filters::filament-dcat-filters.geo.radius') . ' (' . $this->getUnitLabel() . ')')
                        ->placeholder(__('filament-dcat-filters::filament-dcat-filters.geo.radius_placeholder'))
                        ->numeric()
                        ->default($this->defaultRadius),
                ]),
        ]);

        $this->configureQuery();
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $latitude = $data['latitude'] ?? null;
            $longitude = $data['longitude'] ?? null;
            $radius = $data['radius'] ?? $this->defaultRadius;

            if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
                return $query;
            }

            $latitude = (float) $latitude;
            $longitude = (float) $longitude;
            $radius = (float) $radius;

            // Convert radius to kilometers
            $radiusKm = $radius * ($this->unitFactors[$this->unit] ?? 1);

            // Earth's radius in kilometers
            $earthRadius = 6371;

            // Haversine formula for distance calculation
            $latColumn = $this->latitudeColumn;
            $lngColumn = $this->longitudeColumn;

            $haversine = "
                ({$earthRadius} * acos(
                    cos(radians({$latitude}))
                    * cos(radians({$latColumn}))
                    * cos(radians({$lngColumn}) - radians({$longitude}))
                    + sin(radians({$latitude}))
                    * sin(radians({$latColumn}))
                ))
            ";

            return $query->whereRaw("{$haversine} <= ?", [$radiusKm]);
        });

        $this->indicateUsing(function (array $data): array {
            $latitude = $data['latitude'] ?? null;
            $longitude = $data['longitude'] ?? null;
            $radius = $data['radius'] ?? $this->defaultRadius;

            if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
                return [];
            }

            $label = $this->getLabel() ?? __('filament-dcat-filters::filament-dcat-filters.geo.location');
            $unitLabel = $this->getUnitLabel();

            return [
                Indicator::make("{$label}: {$radius} {$unitLabel} " . __('filament-dcat-filters::filament-dcat-filters.geo.from') . " ({$latitude}, {$longitude})")
                    ->removeField('latitude')
                    ->removeField('longitude')
                    ->removeField('radius'),
            ];
        });
    }

    /**
     * Get the latitude column.
     */
    public function getLatitudeColumn(): string
    {
        return $this->latitudeColumn;
    }

    /**
     * Get the longitude column.
     */
    public function getLongitudeColumn(): string
    {
        return $this->longitudeColumn;
    }

    /**
     * Get the default radius.
     */
    public function getDefaultRadius(): float
    {
        return $this->defaultRadius;
    }

    /**
     * Get the unit.
     */
    public function getUnit(): string
    {
        return $this->unit;
    }
}
