<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Cooper\FilamentDcatFilters\Concerns\HasOperator;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class ComparisonFilter extends Filter
{
    use HasColumnName;
    use HasInlineLabel;
    use HasLabelResolver;
    use HasOperator;

    protected const ALLOWED_OPERATORS = ['=', '!=', '>', '>=', '<', '<='];

    protected string $inputType = 'numeric';

    protected ?int $moneyDivider = null;

    protected ?string $moneySuffix = null;

    /**
     * Create a new comparison filter instance.
     */
    public static function make(?string $name = null): static
    {
        $filter = parent::make($name);

        $filter->form([$filter->buildValueInput()]);

        $filter->configureQuery();
        $filter->columnSpan(1);

        return $filter;
    }

    /**
     * Build the value TextInput component.
     */
    protected function buildValueInput(): TextInput
    {
        $labelResolver = $this->labelResolver();

        $input = TextInput::make('value')
            ->label($labelResolver)
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.comparison.placeholder'))
            ->numeric()
            ->live()
            ->columnSpanFull();

        $this->applyInlineLabel($input, $labelResolver);

        return $input;
    }

    /**
     * Set the column name and reconfigure the query.
     */
    public function column(string $column): static
    {
        $this->columnName = $column;
        $this->configureQuery();

        return $this;
    }

    /**
     * Enable money conversion (user input is divided/multiplied before querying).
     * For example, money(100) means: user enters 50 (yuan) → query uses 5000 (cents).
     */
    public function money(int $divideBy = 100): static
    {
        $this->moneyDivider = $divideBy;

        return $this;
    }

    /**
     * Set a display suffix for the money input (e.g., '元').
     */
    public function moneySuffix(string $suffix): static
    {
        $this->moneySuffix = $suffix;

        $this->form([$this->buildValueInput()->suffix($suffix)]);

        return $this;
    }

    /**
     * Set input type to integer.
     */
    public function integer(): static
    {
        $this->inputType = 'integer';

        $this->form([$this->buildValueInput()->integer()]);

        return $this;
    }

    /**
     * Set input type to numeric (allows decimals).
     */
    public function numeric(): static
    {
        $this->inputType = 'numeric';

        $this->form([$this->buildValueInput()->step('any')]);

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

            if (! is_numeric($value)) {
                return $query;
            }

            $column = $this->resolveColumnName();

            // Apply money conversion: multiply user input by divider
            $queryValue = $this->moneyDivider !== null
                ? (float) $value * $this->moneyDivider
                : $value;

            // Validate operator at query time as well
            if (! in_array($this->operator, self::ALLOWED_OPERATORS, true)) {
                return $query;
            }

            return $query->where($column, $this->operator, $queryValue);
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $label = $this->resolveLabel();
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
