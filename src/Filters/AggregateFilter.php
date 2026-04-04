<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Closure;
use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Cooper\FilamentDcatFilters\Concerns\HasOperator;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class AggregateFilter extends Filter
{
    use HasFilterState;
    use HasInlineLabel;
    use HasLabelResolver;
    use HasOperator;

    protected ?string $relationshipName = null;

    protected string $aggregateFunction = 'count';

    protected ?string $aggregateColumn = null;

    protected ?Closure $constraint = null;

    protected function describeState(): FilterStateDescriptor
    {
        return FilterStateDescriptor::make()
            ->fields(['value'])
            ->type(StateType::Single)
            ->emptyWhen(fn (array $data) => ! is_numeric($data['value'] ?? null))
            ->capabilities(['indicator'])
            ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    public function relationship(string $name): static
    {
        $this->relationshipName = $name;
        $this->configureForm();

        return $this;
    }

    public function count(): static
    {
        $this->aggregateFunction = 'count';
        $this->aggregateColumn = null;

        return $this;
    }

    public function sum(string $column): static
    {
        $this->aggregateFunction = 'sum';
        $this->aggregateColumn = $column;

        return $this;
    }

    public function avg(string $column): static
    {
        $this->aggregateFunction = 'avg';
        $this->aggregateColumn = $column;

        return $this;
    }

    public function max(string $column): static
    {
        $this->aggregateFunction = 'max';
        $this->aggregateColumn = $column;

        return $this;
    }

    public function min(string $column): static
    {
        $this->aggregateFunction = 'min';
        $this->aggregateColumn = $column;

        return $this;
    }

    public function constrainedBy(?Closure $constraint): static
    {
        $this->constraint = $constraint;

        return $this;
    }

    public static function countOf(string $relationship): static
    {
        return static::make("{$relationship}_count")
            ->relationship($relationship)
            ->count();
    }

    public static function sumOf(string $relationship, string $column): static
    {
        return static::make("{$relationship}_sum_{$column}")
            ->relationship($relationship)
            ->sum($column);
    }

    protected function configureForm(): void
    {
        if (! $this->relationshipName) {
            return;
        }

        $labelResolver = $this->labelResolver();

        $component = TextInput::make('value')
            ->label($labelResolver)
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.aggregate.placeholder'))
            ->numeric()
            ->columnSpanFull();

        $this->applyInlineLabel($component, $labelResolver);
        $this->form([$component]);
        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            if (! is_numeric($value)) {
                return $query;
            }

            if (! $this->relationshipName) {
                return $query;
            }

            $operator = $this->operator ?? '>=';
            $havingColumn = $this->buildHavingColumn();

            // Build the relationship constraint array
            $relationshipArg = $this->constraint
                ? [$this->relationshipName => $this->constraint]
                : [$this->relationshipName];

            // Apply the aggregate
            $query = match ($this->aggregateFunction) {
                'count' => $query->withCount($relationshipArg),
                'sum' => $query->withSum($relationshipArg, $this->aggregateColumn),
                'avg' => $query->withAvg($relationshipArg, $this->aggregateColumn),
                'max' => $query->withMax($relationshipArg, $this->aggregateColumn),
                'min' => $query->withMin($relationshipArg, $this->aggregateColumn),
                default => $query,
            };

            return $query->having($havingColumn, $operator, $value);
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if (! is_numeric($value)) {
                return [];
            }

            $label = $this->resolveLabel();
            $operator = $this->operator ?? '>=';
            $operatorLabels = [
                '>' => '>',
                '>=' => '≥',
                '<' => '<',
                '<=' => '≤',
                '=' => '=',
                '!=' => '≠',
            ];
            $displayOperator = $operatorLabels[$operator] ?? $operator;

            return [
                Indicator::make("{$label}: {$displayOperator} {$value}")
                    ->removeField('value'),
            ];
        });
    }

    protected function buildHavingColumn(): string
    {
        if ($this->aggregateFunction === 'count') {
            return "{$this->relationshipName}_count";
        }

        return "{$this->relationshipName}_{$this->aggregateFunction}_{$this->aggregateColumn}";
    }

    public function getRelationshipName(): ?string
    {
        return $this->relationshipName;
    }

    public function getAggregateFunction(): string
    {
        return $this->aggregateFunction;
    }

    public function getAggregateColumn(): ?string
    {
        return $this->aggregateColumn;
    }
}
