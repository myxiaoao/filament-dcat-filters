<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class DateComponentFilter extends Filter
{
    protected string $component = 'year'; // 'year', 'month', 'day'

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

        $this->columnSpan(1);
    }

    /**
     * Create a filter for year selection.
     */
    public function year(): static
    {
        $this->component = 'year';

        $currentYear = (int) date('Y');
        $years = [];
        for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
            $years[$i] = (string) $i;
        }

        $this->form([
            Select::make('value')
                ->label(fn (): string => $this->getLabel() ?? __('filament-dcat-filters::filament-dcat-filters.date_component.year'))
                ->options($years)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.date_component.select_year'))
                ->native(false)
                ->columnSpanFull(),
        ]);

        $this->configureQuery('YEAR');

        return $this;
    }

    /**
     * Create a filter for month selection.
     */
    public function month(): static
    {
        $this->component = 'month';

        $months = [
            '01' => __('filament-dcat-filters::filament-dcat-filters.months.01'),
            '02' => __('filament-dcat-filters::filament-dcat-filters.months.02'),
            '03' => __('filament-dcat-filters::filament-dcat-filters.months.03'),
            '04' => __('filament-dcat-filters::filament-dcat-filters.months.04'),
            '05' => __('filament-dcat-filters::filament-dcat-filters.months.05'),
            '06' => __('filament-dcat-filters::filament-dcat-filters.months.06'),
            '07' => __('filament-dcat-filters::filament-dcat-filters.months.07'),
            '08' => __('filament-dcat-filters::filament-dcat-filters.months.08'),
            '09' => __('filament-dcat-filters::filament-dcat-filters.months.09'),
            '10' => __('filament-dcat-filters::filament-dcat-filters.months.10'),
            '11' => __('filament-dcat-filters::filament-dcat-filters.months.11'),
            '12' => __('filament-dcat-filters::filament-dcat-filters.months.12'),
        ];

        $this->form([
            Select::make('value')
                ->label(fn (): string => $this->getLabel() ?? __('filament-dcat-filters::filament-dcat-filters.date_component.month'))
                ->options($months)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.date_component.select_month'))
                ->native(false)
                ->columnSpanFull(),
        ]);

        $this->configureQuery('MONTH');

        return $this;
    }

    /**
     * Create a filter for day selection.
     */
    public function day(): static
    {
        $this->component = 'day';

        $days = [];
        for ($i = 1; $i <= 31; $i++) {
            $day = str_pad((string) $i, 2, '0', STR_PAD_LEFT);
            $days[$day] = $day;
        }

        $this->form([
            Select::make('value')
                ->label(fn (): string => $this->getLabel() ?? __('filament-dcat-filters::filament-dcat-filters.date_component.day'))
                ->options($days)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.date_component.select_day'))
                ->native(false)
                ->columnSpanFull(),
        ]);

        $this->configureQuery('DAY');

        return $this;
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(string $sqlFunction): void
    {
        // Whitelist allowed SQL functions to prevent injection
        $allowedFunctions = ['YEAR', 'MONTH', 'DAY'];
        if (! in_array($sqlFunction, $allowedFunctions, true)) {
            throw new \InvalidArgumentException("Invalid SQL function: {$sqlFunction}");
        }

        $this->query(function (Builder $query, array $data) use ($sqlFunction): Builder {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            $column = $this->columnName ?? $this->getName();

            // Use grammar to safely wrap column name
            $wrappedColumn = $query->getGrammar()->wrap($column);

            return $query->whereRaw("{$sqlFunction}({$wrappedColumn}) = ?", [$value]);
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $label = $this->getLabel() ?? ucfirst($this->component);

            return [
                Indicator::make("{$label}: {$value}")
                    ->removeField('value'),
            ];
        });
    }
}
