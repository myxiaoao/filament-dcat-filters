<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class ComparisonFilter extends Filter
{
    protected string $operator = '=';

    protected string $inputType = 'numeric';

    /**
     * Create a new comparison filter instance.
     */
    public static function make(?string $name = null): static
    {
        $filter = parent::make($name);

        $filter->form([
            TextInput::make('value')
                ->label($filter->getLabel() ?? $filter->getName())
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.comparison.placeholder'))
                ->numeric()
                ->live()
                ->columnSpanFull(),
        ]);

        $filter->configureQuery();
        $filter->columnSpan(1);

        return $filter;
    }

    /**
     * Set operator to greater than (>).
     */
    public function gt(): static
    {
        $this->operator = '>';
        $this->configureQuery();

        return $this;
    }

    /**
     * Set operator to greater than or equal (>=).
     */
    public function gte(): static
    {
        $this->operator = '>=';
        $this->configureQuery();

        return $this;
    }

    /**
     * Set operator to less than (<).
     */
    public function lt(): static
    {
        $this->operator = '<';
        $this->configureQuery();

        return $this;
    }

    /**
     * Set operator to less than or equal (<=).
     */
    public function lte(): static
    {
        $this->operator = '<=';
        $this->configureQuery();

        return $this;
    }

    /**
     * Set operator to equal (=).
     */
    public function eq(): static
    {
        $this->operator = '=';
        $this->configureQuery();

        return $this;
    }

    /**
     * Set operator to not equal (!=).
     */
    public function ne(): static
    {
        $this->operator = '!=';
        $this->configureQuery();

        return $this;
    }

    /**
     * Set input type to integer.
     */
    public function integer(): static
    {
        $this->inputType = 'integer';

        $this->form([
            TextInput::make('value')
                ->label($this->getLabel() ?? $this->getName())
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.comparison.placeholder'))
                ->numeric()
                ->integer()
                ->live()
                ->columnSpanFull(),
        ]);

        return $this;
    }

    /**
     * Set input type to numeric (allows decimals).
     */
    public function numeric(): static
    {
        $this->inputType = 'numeric';

        $this->form([
            TextInput::make('value')
                ->label($this->getLabel() ?? $this->getName())
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.comparison.placeholder'))
                ->numeric()
                ->step('any')
                ->live()
                ->columnSpanFull(),
        ]);

        return $this;
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            $column = $this->getName();

            return $query->where($column, $this->operator, $value);
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $label = $this->getLabel() ?? $this->getName();
            $operatorLabel = $this->getOperatorLabel();

            return [
                Indicator::make("{$label} {$operatorLabel} {$value}")
                    ->removeField('value'),
            ];
        });
    }

    /**
     * Get a human-readable label for the operator.
     */
    protected function getOperatorLabel(): string
    {
        return match ($this->operator) {
            '>' => __('filament-dcat-filters::filament-dcat-filters.comparison.greater_than'),
            '>=' => __('filament-dcat-filters::filament-dcat-filters.comparison.greater_than_or_equal'),
            '<' => __('filament-dcat-filters::filament-dcat-filters.comparison.less_than'),
            '<=' => __('filament-dcat-filters::filament-dcat-filters.comparison.less_than_or_equal'),
            '=' => __('filament-dcat-filters::filament-dcat-filters.comparison.equal_to'),
            '!=' => __('filament-dcat-filters::filament-dcat-filters.comparison.not_equal_to'),
            default => $this->operator,
        };
    }
}
