<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Carbon\Carbon;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class RelativeDateFilter extends Filter
{
    protected array $presets = [];

    protected bool $includeCustomRange = false;

    protected ?string $columnName = null;

    /**
     * Set the column name for the comparison.
     * This allows the filter name to differ from the actual database column.
     */
    public function column(string $column): static
    {
        $this->columnName = $column;

        return $this;
    }

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->presets = $this->getDefaultPresets();

        $this->columnSpan(1);
        $this->configureForm();
    }

    /**
     * Get the default presets.
     */
    protected function getDefaultPresets(): array
    {
        return [
            'today' => __('filament-dcat-filters::filament-dcat-filters.relative_date.today'),
            'yesterday' => __('filament-dcat-filters::filament-dcat-filters.relative_date.yesterday'),
            'last_7_days' => __('filament-dcat-filters::filament-dcat-filters.relative_date.last_7_days'),
            'last_30_days' => __('filament-dcat-filters::filament-dcat-filters.relative_date.last_30_days'),
            'this_week' => __('filament-dcat-filters::filament-dcat-filters.relative_date.this_week'),
            'last_week' => __('filament-dcat-filters::filament-dcat-filters.relative_date.last_week'),
            'this_month' => __('filament-dcat-filters::filament-dcat-filters.relative_date.this_month'),
            'last_month' => __('filament-dcat-filters::filament-dcat-filters.relative_date.last_month'),
            'this_quarter' => __('filament-dcat-filters::filament-dcat-filters.relative_date.this_quarter'),
            'last_quarter' => __('filament-dcat-filters::filament-dcat-filters.relative_date.last_quarter'),
            'this_year' => __('filament-dcat-filters::filament-dcat-filters.relative_date.this_year'),
            'last_year' => __('filament-dcat-filters::filament-dcat-filters.relative_date.last_year'),
        ];
    }

    /**
     * Set custom presets.
     */
    public function presets(array $presets): static
    {
        $this->presets = $presets;
        $this->configureForm();

        return $this;
    }

    /**
     * Add presets to existing ones.
     */
    public function addPresets(array $presets): static
    {
        $this->presets = array_merge($this->presets, $presets);
        $this->configureForm();

        return $this;
    }

    /**
     * Use only specified preset keys.
     */
    public function only(array $keys): static
    {
        $this->presets = array_intersect_key($this->presets, array_flip($keys));
        $this->configureForm();

        return $this;
    }

    /**
     * Exclude specified preset keys.
     */
    public function except(array $keys): static
    {
        $this->presets = array_diff_key($this->presets, array_flip($keys));
        $this->configureForm();

        return $this;
    }

    /**
     * Configure form component.
     */
    protected function configureForm(): void
    {
        $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));

        $this->form([
            Select::make('preset')
                ->label($label)
                ->options($this->presets)
                ->native(false)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.relative_date.placeholder'))
                ->columnSpanFull(),
        ]);

        $this->configureQuery();
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $preset = $data['preset'] ?? null;

            if (! $preset || $preset === '') {
                return $query;
            }

            $column = $this->columnName ?? $this->getName();
            $range = $this->getDateRange($preset);

            if (! $range) {
                return $query;
            }

            [$from, $to] = $range;

            return $query->whereBetween($column, [$from, $to]);
        });

        $this->indicateUsing(function (array $data): array {
            $preset = $data['preset'] ?? null;

            if (! $preset || $preset === '') {
                return [];
            }

            $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));
            $presetLabel = $this->presets[$preset] ?? $preset;

            return [
                Indicator::make("{$label}: {$presetLabel}")
                    ->removeField('preset'),
            ];
        });
    }

    /**
     * Get date range for a preset.
     */
    protected function getDateRange(string $preset): ?array
    {
        $now = Carbon::now();

        return match ($preset) {
            'today' => [
                $now->copy()->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'yesterday' => [
                $now->copy()->subDay()->startOfDay(),
                $now->copy()->subDay()->endOfDay(),
            ],
            'last_7_days' => [
                $now->copy()->subDays(6)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'last_30_days' => [
                $now->copy()->subDays(29)->startOfDay(),
                $now->copy()->endOfDay(),
            ],
            'this_week' => [
                $now->copy()->startOfWeek(),
                $now->copy()->endOfWeek(),
            ],
            'last_week' => [
                $now->copy()->subWeek()->startOfWeek(),
                $now->copy()->subWeek()->endOfWeek(),
            ],
            'this_month' => [
                $now->copy()->startOfMonth(),
                $now->copy()->endOfMonth(),
            ],
            'last_month' => [
                $now->copy()->subMonth()->startOfMonth(),
                $now->copy()->subMonth()->endOfMonth(),
            ],
            'this_quarter' => [
                $now->copy()->startOfQuarter(),
                $now->copy()->endOfQuarter(),
            ],
            'last_quarter' => [
                $now->copy()->subQuarter()->startOfQuarter(),
                $now->copy()->subQuarter()->endOfQuarter(),
            ],
            'this_year' => [
                $now->copy()->startOfYear(),
                $now->copy()->endOfYear(),
            ],
            'last_year' => [
                $now->copy()->subYear()->startOfYear(),
                $now->copy()->subYear()->endOfYear(),
            ],
            default => null,
        };
    }

    /**
     * Quick method to create a filter with common presets only.
     */
    public static function common(?string $name = null): static
    {
        return static::make($name)
            ->only(['today', 'yesterday', 'last_7_days', 'last_30_days', 'this_month', 'this_year']);
    }

    /**
     * Quick method to create a filter with week/month presets.
     */
    public static function weekly(?string $name = null): static
    {
        return static::make($name)
            ->only(['today', 'this_week', 'last_week', 'this_month', 'last_month']);
    }

    /**
     * Quick method to create a filter for reports.
     */
    public static function reporting(?string $name = null): static
    {
        return static::make($name)
            ->only(['this_month', 'last_month', 'this_quarter', 'last_quarter', 'this_year', 'last_year']);
    }
}
