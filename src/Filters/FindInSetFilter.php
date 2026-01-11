<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class FindInSetFilter extends Filter
{
    protected array $options = [];

    protected bool $isMultiple = false;

    protected bool $isSearchable = false;

    protected ?string $placeholder = null;

    protected bool $useMatchAny = false;

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
        $this->configureForm();
    }

    /**
     * Set the available options.
     *
     * @param  array|\Closure  $options
     */
    public function options(array|\Closure $options): static
    {
        $this->options = is_callable($options) ? $options() : $options;
        $this->configureForm();

        return $this;
    }

    /**
     * Allow multiple selections.
     */
    public function multiple(bool $condition = true): static
    {
        $this->isMultiple = $condition;
        $this->configureForm();

        return $this;
    }

    /**
     * Make the select searchable.
     */
    public function searchable(bool $condition = true): static
    {
        $this->isSearchable = $condition;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the placeholder text.
     */
    public function placeholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;
        $this->configureForm();

        return $this;
    }

    /**
     * Match any of the selected values (OR logic).
     * Default behavior when multiple() is used.
     */
    public function matchAny(bool $condition = true): static
    {
        $this->useMatchAny = $condition;

        return $this;
    }

    /**
     * Match all of the selected values (AND logic).
     */
    public function matchAll(): static
    {
        $this->useMatchAny = false;

        return $this;
    }

    /**
     * Configure form component.
     */
    protected function configureForm(): void
    {
        $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));
        $placeholder = $this->placeholder ?? ($this->isMultiple
            ? __('filament-dcat-filters::filament-dcat-filters.find_in_set.placeholder_multiple')
            : __('filament-dcat-filters::filament-dcat-filters.find_in_set.placeholder_single'));

        $this->form([
            Select::make('value')
                ->label($label)
                ->options($this->options)
                ->placeholder($placeholder)
                ->multiple($this->isMultiple)
                ->searchable($this->isSearchable)
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
            $value = $data['value'] ?? null;

            if (empty($value)) {
                return $query;
            }

            $column = $this->getName();
            $values = Arr::wrap($value);

            if (count($values) === 1) {
                // Single value - simple FIND_IN_SET
                return $query->whereRaw('FIND_IN_SET(?, ' . $column . ')', [$values[0]]);
            }

            // Multiple values
            if ($this->useMatchAny) {
                // OR logic - match any of the selected values
                return $query->where(function (Builder $q) use ($column, $values) {
                    foreach ($values as $val) {
                        $q->orWhereRaw('FIND_IN_SET(?, ' . $column . ')', [$val]);
                    }
                });
            }

            // AND logic - match all of the selected values
            foreach ($values as $val) {
                $query->whereRaw('FIND_IN_SET(?, ' . $column . ')', [$val]);
            }

            return $query;
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if (empty($value)) {
                return [];
            }

            $values = Arr::wrap($value);
            $labels = [];

            foreach ($values as $val) {
                $labels[] = $this->options[$val] ?? $val;
            }

            $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));
            $separator = $this->useMatchAny
                ? ' ' . __('filament-dcat-filters::filament-dcat-filters.find_in_set.or') . ' '
                : ' ' . __('filament-dcat-filters::filament-dcat-filters.find_in_set.and') . ' ';

            return [
                Indicator::make("{$label}: " . implode($separator, $labels))
                    ->removeField('value'),
            ];
        });
    }
}
