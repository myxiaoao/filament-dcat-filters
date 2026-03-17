<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class FilterGroup extends Filter
{
    protected string $logic = 'and';

    /** @var array<Filter> */
    protected array $childFilters = [];

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
        $this->configureForm();
        $this->configureQuery();

        return $this;
    }

    protected function configureForm(): void
    {
        $formComponents = array_merge(
            ...array_map(fn (Filter $filter) => $filter->getFormSchema(), $this->childFilters)
        );

        $this->form($formComponents);
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            if (empty($this->childFilters)) {
                return $query;
            }

            $hasData = collect($data)->contains(fn ($value) => $value !== null && $value !== '');

            if (! $hasData) {
                return $query;
            }

            if ($this->logic === 'or') {
                return $query->where(function (Builder $q) use ($data) {
                    $isFirst = true;
                    foreach ($this->childFilters as $filter) {
                        $value = $data[$filter->getName()] ?? null;

                        if ($value === null || $value === '') {
                            continue;
                        }

                        if ($isFirst) {
                            $this->applyFilterQuery($q, $filter, $value);
                            $isFirst = false;
                        } else {
                            $q->orWhere(fn (Builder $subQ) => $this->applyFilterQuery($subQ, $filter, $value));
                        }
                    }
                });
            }

            foreach ($this->childFilters as $filter) {
                $value = $data[$filter->getName()] ?? null;

                if ($value !== null && $value !== '') {
                    $this->applyFilterQuery($query, $filter, $value);
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
                    $indicators[] = Indicator::make("{$label}: {$value}")
                        ->removeField($filterName);
                }
            }

            return $indicators;
        });
    }

    protected function applyFilterQuery(Builder $query, Filter $filter, mixed $value): void
    {
        if ($value === null || $value === '') {
            return;
        }

        $filter->apply($query, ['value' => $value, 'isActive' => true]);
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
