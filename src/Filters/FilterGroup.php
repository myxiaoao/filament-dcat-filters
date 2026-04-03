<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class FilterGroup extends Filter
{
    protected const MAX_NESTING_DEPTH = 5;

    protected string $logic = 'and';

    /** @var array<Filter> */
    protected array $childFilters = [];

    protected int $nestingDepth = 0;

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    public function logic(string $logic): static
    {
        $this->logic = strtolower($logic) === 'or' ? 'or' : 'and';
        $this->configureQuery();

        return $this;
    }

    public function andLogic(): static
    {
        return $this->logic('and');
    }

    public function orLogic(): static
    {
        return $this->logic('or');
    }

    /** @param array<Filter> $filters */
    public function filters(array $filters): static
    {
        $this->childFilters = $filters;

        // Validate nesting depth recursively
        foreach ($filters as $filter) {
            if ($filter instanceof self) {
                $childMaxDepth = $this->getMaxChildDepth($filter);

                if ($this->nestingDepth + 1 + $childMaxDepth >= self::MAX_NESTING_DEPTH) {
                    throw new \InvalidArgumentException(
                        sprintf('FilterGroup nesting depth exceeds maximum of %d.', self::MAX_NESTING_DEPTH)
                    );
                }
            }
        }

        $this->configureForm();
        $this->configureQuery();

        return $this;
    }

    /**
     * Recursively calculate the maximum depth of nested FilterGroups.
     */
    protected function getMaxChildDepth(self $group): int
    {
        $maxDepth = 0;

        foreach ($group->getChildFilters() as $child) {
            if ($child instanceof self) {
                $maxDepth = max($maxDepth, 1 + $this->getMaxChildDepth($child));
            }
        }

        return $maxDepth;
    }

    /**
     * Build namespaced form: each child filter's fields are wrapped under a fieldset
     * keyed by the filter name, preventing field name collisions.
     */
    protected function configureForm(): void
    {
        $formComponents = [];

        foreach ($this->childFilters as $filter) {
            $filterName = $filter->getName();
            $schema = $filter->getFormSchema();

            // Wrap each filter's fields in a Fieldset keyed by filter name
            // This creates a nested state: data[filterName][fieldName]
            $formComponents[] = \Filament\Schemas\Components\Fieldset::make(
                $filter->getLabel() ?? ucfirst(str_replace('_', ' ', $filterName))
            )
                ->schema($schema)
                ->statePath($filterName)
                ->columns(1)
                ->columnSpan(1);
        }

        $this->form($formComponents);
    }

    /**
     * Extract filter data from the namespaced state structure.
     */
    protected function getFilterData(array $data, string $filterName): mixed
    {
        return $data[$filterName] ?? null;
    }

    /**
     * Check if filter data has any non-empty values.
     */
    protected function hasFilterValues(mixed $filterData): bool
    {
        if ($filterData === null) {
            return false;
        }

        if (is_array($filterData)) {
            return collect($filterData)->contains(fn ($v) => $v !== null && $v !== '');
        }

        return $filterData !== '' && $filterData !== null;
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            if (empty($this->childFilters)) {
                return $query;
            }

            // Check if any filter has data in the namespaced structure
            $hasAnyData = false;
            foreach ($this->childFilters as $filter) {
                if ($this->hasFilterValues($this->getFilterData($data, $filter->getName()))) {
                    $hasAnyData = true;

                    break;
                }
            }

            if (! $hasAnyData) {
                return $query;
            }

            if ($this->logic === 'or') {
                return $query->where(function (Builder $q) use ($data) {
                    $isFirst = true;
                    foreach ($this->childFilters as $filter) {
                        $filterData = $this->getFilterData($data, $filter->getName());

                        if (! $this->hasFilterValues($filterData)) {
                            continue;
                        }

                        if ($isFirst) {
                            $this->applyFilterQuery($q, $filter, $filterData);
                            $isFirst = false;
                        } else {
                            $q->orWhere(fn (Builder $subQ) => $this->applyFilterQuery($subQ, $filter, $filterData));
                        }
                    }
                });
            }

            foreach ($this->childFilters as $filter) {
                $filterData = $this->getFilterData($data, $filter->getName());

                if ($this->hasFilterValues($filterData)) {
                    $this->applyFilterQuery($query, $filter, $filterData);
                }
            }

            return $query;
        });

        $this->indicateUsing(function (array $data): array {
            $indicators = [];

            foreach ($this->childFilters as $filter) {
                $filterName = $filter->getName();
                $filterData = $this->getFilterData($data, $filterName);

                if (! $this->hasFilterValues($filterData)) {
                    continue;
                }

                $label = $filter->getLabel() ?? ucfirst(str_replace('_', ' ', $filterName));

                // For array data (e.g. RangeFilter from/to), show the non-empty values
                if (is_array($filterData)) {
                    $displayValues = collect($filterData)
                        ->filter(fn ($v) => $v !== null && $v !== '')
                        ->implode(' ~ ');

                    if ($displayValues !== '') {
                        $indicators[] = Indicator::make("{$label}: {$displayValues}")
                            ->removeField($filterName);
                    }
                } else {
                    $indicators[] = Indicator::make("{$label}: {$filterData}")
                        ->removeField($filterName);
                }
            }

            return $indicators;
        });
    }

    /**
     * Apply a child filter's query using the namespaced data.
     *
     * Data is already in the child filter's own format (e.g. ['value' => 'x']
     * for LikeFilter, ['from' => '1', 'to' => '10'] for RangeFilter).
     */
    protected function applyFilterQuery(Builder $query, Filter $filter, mixed $filterData): void
    {
        if (! $this->hasFilterValues($filterData)) {
            return;
        }

        // The namespaced data is already in the child filter's native format
        if (is_array($filterData)) {
            $filter->apply($query, array_merge($filterData, ['isActive' => true]));

            return;
        }

        // Scalar value: wrap in the child filter's expected field name
        $formSchema = $filter->getFormSchema();
        $fieldName = ! empty($formSchema) ? $formSchema[0]->getName() : 'value';
        $filter->apply($query, [$fieldName => $filterData, 'isActive' => true]);
    }

    public function getLogic(): string
    {
        return $this->logic;
    }

    /** @return array<Filter> */
    public function getChildFilters(): array
    {
        return $this->childFilters;
    }
}
