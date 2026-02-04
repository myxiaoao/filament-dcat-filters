<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class InFilter extends Filter
{
    protected array $options = [];

    protected bool $multiple = false;

    protected bool $searchable = false;

    protected bool $negate = false;

    protected ?string $columnName = null;

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    /**
     * Set the available options for this filter.
     */
    public function options(array $options): static
    {
        $this->options = $options;
        $this->configureForm();

        return $this;
    }

    /**
     * Enable multiple selection (uses CheckboxList).
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        $this->configureForm();

        return $this;
    }

    /**
     * Enable searchable dropdown.
     */
    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        $this->configureForm();

        return $this;
    }

    /**
     * Negate the filter (NOT IN).
     */
    public function negate(): static
    {
        $this->negate = true;

        return $this;
    }

    /**
     * Alias for negate() - use NOT IN operator.
     */
    public function notIn(): static
    {
        return $this->negate();
    }

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
     * Configure form component based on settings.
     */
    protected function configureForm(): void
    {
        if (empty($this->options)) {
            return;
        }

        // Use Select component uniformly, supporting both single and multiple selection
        $this->form([
            Select::make($this->multiple ? 'values' : 'value')
                ->label(fn (): string => $this->getLabel() ?? $this->getName())
                ->options($this->options)
                ->searchable($this->searchable)
                ->multiple($this->multiple)
                ->native(false)
                ->placeholder($this->multiple ? __('filament-dcat-filters::filament-dcat-filters.in.placeholder_multiple') : __('filament-dcat-filters::filament-dcat-filters.in.placeholder_single'))
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
            // Use custom column name if set, otherwise default to filter name
            $column = $this->columnName ?? $this->getName();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return $query;
                }

                // Handle NOT IN for multiple values
                if ($this->negate) {
                    return $query->whereNotIn($column, $values);
                }

                return $query->whereIn($column, $values);
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            // Handle NOT EQUAL for single value
            if ($this->negate) {
                return $query->where($column, '!=', $value);
            }

            return $query->where($column, $value);
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->getLabel() ?? $this->getName();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return [];
                }

                $labels = array_map(fn ($value) => $this->options[$value] ?? $value, $values);

                return [
                    Indicator::make("{$label}: ".implode(', ', $labels))
                        ->removeField('values'),
                ];
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $valueLabel = $this->options[$value] ?? $value;

            return [
                Indicator::make("{$label}: {$valueLabel}")
                    ->removeField('value'),
            ];
        });
    }
}
