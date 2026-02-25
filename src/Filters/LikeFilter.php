<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasDatabaseDriver;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasRelationship;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class LikeFilter extends Filter
{
    use HasDatabaseDriver;
    use HasInlineLabel;
    use HasRelationship;

    protected string $operator = 'like';

    protected bool $caseSensitive = false;

    protected string $wildcardPosition = 'both'; // 'both', 'start', 'end', 'none'

    protected bool $negate = false;

    protected ?string $columnName = null;

    /**
     * Create a new LIKE filter instance.
     */
    public static function make(?string $name = null): static
    {
        $filter = parent::make($name);

        $filter->operator = config('filament-dcat-filters.quick_filters.like_operator', 'like');
        $filter->caseSensitive = config('filament-dcat-filters.quick_filters.case_sensitive', false);
        $filter->wildcardPosition = config('filament-dcat-filters.quick_filters.like_wildcards', 'both');

        $labelResolver = fn (): string => $filter->getLabel() ?? $filter->getName();

        $input = TextInput::make('value')
            ->label($labelResolver)
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.like.placeholder'))
            ->live(debounce: 500)
            ->columnSpanFull();

        $filter->applyInlineLabel($input, $labelResolver);

        $filter->form([$input]);

        $filter->configureQuery();
        $filter->columnSpan(1);

        return $filter;
    }

    /**
     * Set the LIKE operator (like or ilike).
     */
    public function operator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Use case-insensitive LIKE (ilike in PostgreSQL, or LOWER() in others).
     */
    public function insensitive(): static
    {
        $this->caseSensitive = false;

        return $this;
    }

    /**
     * Use case-sensitive LIKE.
     */
    public function sensitive(): static
    {
        $this->caseSensitive = true;

        return $this;
    }

    /**
     * Add wildcards at both ends (default).
     */
    public function wildcards(string $position = 'both'): static
    {
        $this->wildcardPosition = $position;

        return $this;
    }

    /**
     * Search starts with the value.
     */
    public function startsWith(): static
    {
        $this->wildcardPosition = 'end';

        return $this;
    }

    /**
     * Search ends with the value.
     */
    public function endsWith(): static
    {
        $this->wildcardPosition = 'start';

        return $this;
    }

    /**
     * Search for exact pattern (no wildcards).
     */
    public function exact(): static
    {
        $this->wildcardPosition = 'none';

        return $this;
    }

    /**
     * Negate the filter (NOT LIKE).
     */
    public function negate(): static
    {
        $this->negate = true;

        return $this;
    }

    /**
     * Alias for negate() - use NOT LIKE operator.
     */
    public function notLike(): static
    {
        return $this->negate();
    }

    /**
     * Set the column name for the comparison.
     * This allows the filter name to differ from the actual database column.
     */
    public function column(string $column): static
    {
        $this->columnName = $column;

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

            // Use relationship title column, custom column, or filter name
            $column = $this->relationshipTitleColumn ?? $this->columnName ?? $this->getName();
            $pattern = $this->buildPattern((string) $value);

            // Relationship query: wrap constraint inside whereHas
            if ($this->hasRelationship()) {
                return $this->applyRelationshipLike($query, $column, $pattern);
            }

            if (! $this->caseSensitive) {
                // PostgreSQL: use native ILIKE/NOT ILIKE
                if ($this->isPostgres($query)) {
                    $operator = $this->negate ? 'not ilike' : 'ilike';

                    return $query->where($column, $operator, $pattern);
                }

                // MySQL/SQLite: use LOWER() + like
                $operator = $this->negate ? 'not like' : 'like';

                return $query->whereRaw(
                    'LOWER('.$query->getGrammar()->wrap($column).') '.$operator.' ?',
                    [strtolower($pattern)]
                );
            }

            // Handle negation for case-sensitive search
            if ($this->negate) {
                return $query->where($column, 'not like', $pattern);
            }

            return $query->where($column, $this->operator, $pattern);
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $label = $this->getLabel() ?? $this->getName();

            return [
                Indicator::make("{$label}: {$value}")
                    ->removeField('value'),
            ];
        });
    }

    /**
     * Apply a LIKE constraint through a relationship using whereHas.
     */
    protected function applyRelationshipLike(Builder $query, string $column, string $pattern): Builder
    {
        return $query->whereHas($this->relationshipName, function (Builder $q) use ($column, $pattern) {
            if (! $this->caseSensitive) {
                if ($this->isPostgres($q)) {
                    $operator = $this->negate ? 'not ilike' : 'ilike';
                    $q->where($column, $operator, $pattern);

                    return;
                }

                $operator = $this->negate ? 'not like' : 'like';
                $q->whereRaw(
                    'LOWER('.$q->getGrammar()->wrap($column).') '.$operator.' ?',
                    [strtolower($pattern)]
                );

                return;
            }

            if ($this->negate) {
                $q->where($column, 'not like', $pattern);

                return;
            }

            $q->where($column, $this->operator, $pattern);
        });
    }

    /**
     * Escape special LIKE characters to prevent unintended pattern matching.
     */
    protected function escapeLikeValue(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value
        );
    }

    /**
     * Build the LIKE pattern with wildcards.
     */
    protected function buildPattern(string $value): string
    {
        $escapedValue = $this->escapeLikeValue($value);

        return match ($this->wildcardPosition) {
            'start' => "%{$escapedValue}",
            'end' => "{$escapedValue}%",
            'none' => $escapedValue,
            default => "%{$escapedValue}%",
        };
    }
}
