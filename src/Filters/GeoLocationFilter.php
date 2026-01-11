<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class GeoLocationFilter extends Filter
{
    protected const UNIT_FACTORS = [
        'km' => 1,
        'mi' => 1.60934,
        'm' => 0.001,
    ];

    protected const EARTH_RADIUS_KM = 6371;

    protected string $latitudeColumn = 'latitude';

    protected string $longitudeColumn = 'longitude';

    protected float $defaultRadius = 10;

    protected string $unit = 'km';

    protected ?float $centerLatitude = null;

    protected ?float $centerLongitude = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(2);
        $this->configureForm();
    }

    public function latitude(string $column): static
    {
        $this->latitudeColumn = $column;

        return $this;
    }

    public function longitude(string $column): static
    {
        $this->longitudeColumn = $column;

        return $this;
    }

    public function coordinates(string $latitudeColumn, string $longitudeColumn): static
    {
        $this->latitudeColumn = $latitudeColumn;
        $this->longitudeColumn = $longitudeColumn;

        return $this;
    }

    public function radius(float $radius, string $unit = 'km'): static
    {
        $this->defaultRadius = $radius;
        $this->unit = strtolower($unit);

        return $this;
    }

    public function center(float $latitude, float $longitude): static
    {
        $this->centerLatitude = $latitude;
        $this->centerLongitude = $longitude;

        return $this;
    }

    public function kilometers(): static
    {
        $this->unit = 'km';

        return $this;
    }

    public function miles(): static
    {
        $this->unit = 'mi';

        return $this;
    }

    public function meters(): static
    {
        $this->unit = 'm';

        return $this;
    }

    protected function getUnitLabel(): string
    {
        return match ($this->unit) {
            'km' => __('filament-dcat-filters::filament-dcat-filters.geo.km'),
            'mi' => __('filament-dcat-filters::filament-dcat-filters.geo.mi'),
            'm' => __('filament-dcat-filters::filament-dcat-filters.geo.m'),
            default => $this->unit,
        };
    }

    public function getLatitudeColumn(): string
    {
        return $this->latitudeColumn;
    }

    public function getLongitudeColumn(): string
    {
        return $this->longitudeColumn;
    }

    public function getDefaultRadius(): float
    {
        return $this->defaultRadius;
    }

    public function getUnit(): string
    {
        return $this->unit;
    }

    protected function configureForm(): void
    {
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
                        ->label(__('filament-dcat-filters::filament-dcat-filters.geo.radius').' ('.$this->getUnitLabel().')')
                        ->placeholder(__('filament-dcat-filters::filament-dcat-filters.geo.radius_placeholder'))
                        ->numeric()
                        ->default($this->defaultRadius),
                ]),
        ]);

        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $latitude = $data['latitude'] ?? null;
            $longitude = $data['longitude'] ?? null;

            if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
                return $query;
            }

            $latitude = (float) $latitude;
            $longitude = (float) $longitude;
            $radius = (float) ($data['radius'] ?? $this->defaultRadius);
            $radiusKm = $radius * (self::UNIT_FACTORS[$this->unit] ?? 1);

            $haversine = sprintf(
                '(%d * acos(cos(radians(%f)) * cos(radians(%s)) * cos(radians(%s) - radians(%f)) + sin(radians(%f)) * sin(radians(%s))))',
                self::EARTH_RADIUS_KM,
                $latitude,
                $this->latitudeColumn,
                $this->longitudeColumn,
                $longitude,
                $latitude,
                $this->latitudeColumn
            );

            return $query->whereRaw("{$haversine} <= ?", [$radiusKm]);
        });

        $this->indicateUsing(function (array $data): array {
            $latitude = $data['latitude'] ?? null;
            $longitude = $data['longitude'] ?? null;

            if ($latitude === null || $longitude === null || $latitude === '' || $longitude === '') {
                return [];
            }

            $radius = $data['radius'] ?? $this->defaultRadius;
            $label = $this->getLabel() ?? __('filament-dcat-filters::filament-dcat-filters.geo.location');
            $from = __('filament-dcat-filters::filament-dcat-filters.geo.from');

            return [
                Indicator::make("{$label}: {$radius} {$this->getUnitLabel()} {$from} ({$latitude}, {$longitude})")
                    ->removeField('latitude')
                    ->removeField('longitude')
                    ->removeField('radius'),
            ];
        });
    }
}
