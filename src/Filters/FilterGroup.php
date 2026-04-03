<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class FilterGroup extends Filter
{
    use HasFilterState;

    protected const MAX_NESTING_DEPTH = 5;

    protected string $logic = 'and';

    /** @var array<Filter> */
    protected array $childFilters = [];

    protected int $nestingDepth = 0;

    protected function describeState(): FilterStateDescriptor
    {
        return FilterStateDescriptor::make()
            ->fields(array_map(fn ($f) => $f->getName(), $this->childFilters))
            ->type(StateType::Composite)
            ->emptyWhen(function (array $data) {
                foreach ($this->childFilters as $filter) {
                    $filterData = $data[$filter->getName()] ?? null;

                    if ($filterData === null || $filterData === '' || $filterData === []) {
                        continue;
                    }

                    if (is_array($filterData) && collect($filterData)->contains(fn ($v) => $v !== null && $v !== '')) {
                        return false;
                    }

                    if (! is_array($filterData)) {
                        return false;
                    }
                }

                return true;
            })
            ->capabilities(['indicator'])
            ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
    }

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
     * Check if a child filter's namespaced data has any values, using its descriptor.
     */
    protected function isChildFilterEmpty(Filter $filter, array $data): bool
    {
        $filterData = $data[$filter->getName()] ?? null;

        if ($filterData === null || $filterData === '' || $filterData === []) {
            return true;
        }

        if (method_exists($filter, 'getStateDescriptor')) {
            return $filter->getStateDescriptor()->isEmpty(
                is_array($filterData) ? $filterData : []
            );
        }

        // Fallback for filters without HasFilterState
        if (is_array($filterData)) {
            return ! collect($filterData)->contains(fn ($v) => $v !== null && $v !== '');
        }

        return false;
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            if (empty($this->childFilters)) {
                return $query;
            }

            // Check if any child filter has data
            $hasAnyData = false;
            foreach ($this->childFilters as $filter) {
                if (! $this->isChildFilterEmpty($filter, $data)) {
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
                        if ($this->isChildFilterEmpty($filter, $data)) {
                            continue;
                        }

                        $filterData = $data[$filter->getName()] ?? [];

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
                if (! $this->isChildFilterEmpty($filter, $data)) {
                    $this->applyFilterQuery($query, $filter, $data[$filter->getName()] ?? []);
                }
            }

            return $query;
        });

        $this->indicateUsing(function (array $data): array {
            $indicators = [];

            foreach ($this->childFilters as $filter) {
                $filterName = $filter->getName();

                if ($this->isChildFilterEmpty($filter, $data)) {
                    continue;
                }

                $filterData = $data[$filterName] ?? [];
                $label = $filter->getLabel() ?? ucfirst(str_replace('_', ' ', $filterName));

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
     * Uses the child's state descriptor to determine the primary field name.
     */
    protected function applyFilterQuery(Builder $query, Filter $filter, mixed $filterData): void
    {
        if (is_array($filterData)) {
            $filter->apply($query, array_merge($filterData, ['isActive' => true]));

            return;
        }

        // Scalar value: use descriptor to get field name, fallback to 'value'
        $fieldName = 'value';
        if (method_exists($filter, 'getStateDescriptor')) {
            $fields = $filter->getStateDescriptor()->getFields();
            $fieldName = $fields[0] ?? 'value';
        }

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
