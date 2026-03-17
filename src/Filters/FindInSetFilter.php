<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasDatabaseDriver;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;

class FindInSetFilter extends Filter
{
    use HasColumnName;
    use HasDatabaseDriver;
    use HasInlineLabel;
    use HasLabelResolver;

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
        $this->configureForm();

        return $this;
    }

    public function multiple(bool $condition = true): static
    {
        $this->isMultiple = $condition;
        $this->configureForm();

        return $this;
    }

    public function searchable(bool $condition = true): static
    {
        $this->isSearchable = $condition;
        $this->configureForm();

        return $this;
    }

    public function placeholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;
        $this->configureForm();

        return $this;
    }

    public function matchAny(bool $condition = true): static
    {
        $this->useMatchAny = $condition;
        $this->configureForm();

        return $this;
    }

    public function matchAll(): static
    {
        $this->useMatchAny = false;
        $this->configureForm();

        return $this;
    }

    /**
     * Generate a FIND_IN_SET expression adapted to the database driver.
     * PostgreSQL: ? = ANY(string_to_array(column, ','))
     * MySQL: FIND_IN_SET(?, column)
     */
    protected function findInSetExpression(Builder $query, string $column, mixed $value): Builder
    {
        if ($this->isPostgres($query)) {
            return $query->whereRaw(
                '? = ANY(string_to_array('.$query->getGrammar()->wrap($column).", ','))",
                [$value]
            );
        }

        return $query->whereRaw(
            'FIND_IN_SET(?, '.$query->getGrammar()->wrap($column).')',
            [$value]
        );
    }

    /**
     * Generate an OR FIND_IN_SET expression adapted to the database driver.
     */
    protected function orFindInSetExpression(Builder $query, string $column, mixed $value): Builder
    {
        if ($this->isPostgres($query)) {
            return $query->orWhereRaw(
                '? = ANY(string_to_array('.$query->getGrammar()->wrap($column).", ','))",
                [$value]
            );
        }

        return $query->orWhereRaw(
            'FIND_IN_SET(?, '.$query->getGrammar()->wrap($column).')',
            [$value]
        );
    }

    protected function configureForm(): void
    {
        $labelResolver = $this->labelResolver();
        $placeholder = $this->placeholder ?? __('filament-dcat-filters::filament-dcat-filters.find_in_set.placeholder_'.($this->isMultiple ? 'multiple' : 'single'));

        $component = Select::make('value')
            ->label($labelResolver)
            ->options($this->options)
            ->placeholder($placeholder)
            ->multiple($this->isMultiple)
            ->searchable($this->isSearchable)
            ->columnSpanFull();

        $this->applyInlineLabel($component, $labelResolver);

        $this->form([
            $component,
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

            $column = $this->resolveColumnName();
            $values = Arr::wrap($value);

            if (count($values) === 1) {
                return $this->findInSetExpression($query, $column, $values[0]);
            }

            if ($this->useMatchAny) {
                return $query->where(function (Builder $q) use ($column, $values) {
                    foreach ($values as $val) {
                        $this->orFindInSetExpression($q, $column, $val);
                    }
                });
            }

            foreach ($values as $val) {
                $this->findInSetExpression($query, $column, $val);
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
            $filterLabel = $this->resolveLabel();
            $separator = ' '.__('filament-dcat-filters::filament-dcat-filters.find_in_set.'.($this->useMatchAny ? 'or' : 'and')).' ';

            return [
                Indicator::make("{$filterLabel}: ".implode($separator, $labels))
                    ->removeField('value'),
            ];
        });
    }
}
