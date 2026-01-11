<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class FilterGroup extends Filter
{
    protected string $logic = 'and';

    protected array $childFilters = [];

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    /**
     * Set the logic operator (and/or).
     */
    public function logic(string $logic): static
    {
        $this->logic = strtolower($logic) === 'or' ? 'or' : 'and';
        $this->configureQuery();

        return $this;
    }

    /**
     * Use AND logic for combining filters.
     */
    public function andLogic(): static
    {
        return $this->logic('and');
    }

    /**
     * Use OR logic for combining filters.
     */
    public function orLogic(): static
    {
        return $this->logic('or');
    }

    /**
     * Set the filters in this group.
     *
     * @param  array<Filter>  $filters
     */
    public function filters(array $filters): static
    {
        $this->childFilters = $filters;
        $this->configureForm();
        $this->configureQuery();

        return $this;
    }

    /**
     * Configure form by combining child filter forms.
     */
    protected function configureForm(): void
    {
        $formComponents = [];

        foreach ($this->childFilters as $filter) {
            $schema = $filter->getFormSchema();
            foreach ($schema as $component) {
                $formComponents[] = $component;
            }
        }

        $this->form($formComponents);
    }

    /**
     * Configure the query logic for this filter group.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            if (empty($this->childFilters)) {
                return $query;
            }

            // Check if any filter has data
            $hasData = false;
            foreach ($data as $value) {
                if ($value !== null && $value !== '') {
                    $hasData = true;
                    break;
                }
            }

            if (! $hasData) {
                return $query;
            }

            if ($this->logic === 'or') {
                // OR logic: wrap all conditions in orWhere
                return $query->where(function (Builder $q) use ($data) {
                    $isFirst = true;
                    foreach ($this->childFilters as $filter) {
                        $filterName = $filter->getName();
                        $filterData = ['value' => $data[$filterName] ?? null];

                        if ($filterData['value'] !== null && $filterData['value'] !== '') {
                            if ($isFirst) {
                                $this->applyFilterQuery($q, $filter, $filterData);
                                $isFirst = false;
                            } else {
                                $q->orWhere(function (Builder $subQ) use ($filter, $filterData) {
                                    $this->applyFilterQuery($subQ, $filter, $filterData);
                                });
                            }
                        }
                    }
                });
            }

            // AND logic (default): apply filters normally
            foreach ($this->childFilters as $filter) {
                $filterName = $filter->getName();
                $filterData = ['value' => $data[$filterName] ?? null];

                if ($filterData['value'] !== null && $filterData['value'] !== '') {
                    $this->applyFilterQuery($query, $filter, $filterData);
                }
            }

            return $query;
        });

        $this->indicateUsing(function (array $data): array {
            $indicators = [];

            foreach ($this->childFilters as $filter) {
                $filterName = $filter->getName();
                $value = $data[$filterName] ?? null;

                if ($value !== null && $value !== '') {
                    $label = $filter->getLabel() ?? ucfirst(str_replace('_', ' ', $filterName));
                    $indicators[] = \Filament\Tables\Filters\Indicator::make("{$label}: {$value}")
                        ->removeField($filterName);
                }
            }

            return $indicators;
        });
    }

    /**
     * Apply a filter's query to the given query builder.
     */
    protected function applyFilterQuery(Builder $query, Filter $filter, array $data): void
    {
        // Get the filter's query callback and apply it
        $filterName = $filter->getName();
        $column = $filterName;
        $value = $data['value'] ?? null;

        if ($value === null || $value === '') {
            return;
        }

        // Default behavior: simple where clause
        // In a real implementation, this would call the filter's actual query method
        $query->where($column, 'like', "%{$value}%");
    }

    /**
     * Get the logic type.
     */
    public function getLogic(): string
    {
        return $this->logic;
    }

    /**
     * Get the child filters.
     *
     * @return array<Filter>
     */
    public function getChildFilters(): array
    {
        return $this->childFilters;
    }
}
