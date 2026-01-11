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

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
        $this->configureForm();
    }

    public function options(array|\Closure $options): static
    {
        $this->options = is_callable($options) ? $options() : $options;

        return $this;
    }

    public function multiple(bool $condition = true): static
    {
        $this->isMultiple = $condition;

        return $this;
    }

    public function searchable(bool $condition = true): static
    {
        $this->isSearchable = $condition;

        return $this;
    }

    public function placeholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function matchAny(bool $condition = true): static
    {
        $this->useMatchAny = $condition;

        return $this;
    }

    public function matchAll(): static
    {
        $this->useMatchAny = false;

        return $this;
    }

    protected function configureForm(): void
    {
        $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));
        $placeholder = $this->placeholder ?? __('filament-dcat-filters::filament-dcat-filters.find_in_set.placeholder_'.($this->isMultiple ? 'multiple' : 'single'));

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
                return $query->whereRaw('FIND_IN_SET(?, '.$column.')', [$values[0]]);
            }

            if ($this->useMatchAny) {
                return $query->where(function (Builder $q) use ($column, $values) {
                    foreach ($values as $val) {
                        $q->orWhereRaw('FIND_IN_SET(?, '.$column.')', [$val]);
                    }
                });
            }

            foreach ($values as $val) {
                $query->whereRaw('FIND_IN_SET(?, '.$column.')', [$val]);
            }

            return $query;
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if (empty($value)) {
                return [];
            }

            $values = Arr::wrap($value);
            $labels = array_map(fn ($val) => $this->options[$val] ?? $val, $values);
            $filterLabel = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));
            $separator = ' '.__('filament-dcat-filters::filament-dcat-filters.find_in_set.'.($this->useMatchAny ? 'or' : 'and')).' ';

            return [
                Indicator::make("{$filterLabel}: ".implode($separator, $labels))
                    ->removeField('value'),
            ];
        });
    }
}
