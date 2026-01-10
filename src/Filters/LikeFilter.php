<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class LikeFilter extends Filter
{
    protected string $operator = 'like';

    protected bool $caseSensitive = false;

    protected string $wildcardPosition = 'both'; // 'both', 'start', 'end', 'none'

    protected bool $negate = false;

    /**
     * Create a new LIKE filter instance.
     */
    public static function make(?string $name = null): static
    {
        $filter = parent::make($name);

        $filter->operator = config('filament-dcat-filters.quick_filters.like_operator', 'like');
        $filter->caseSensitive = config('filament-dcat-filters.quick_filters.case_sensitive', false);
        $filter->wildcardPosition = config('filament-dcat-filters.quick_filters.like_wildcards', 'both');

        $filter->form([
            TextInput::make('value')
                ->label($filter->getLabel() ?? $filter->getName())
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.like.placeholder'))
                ->live(debounce: 500)
                ->columnSpanFull(),
        ]);

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
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            $column = $this->getName();
            $pattern = $this->buildPattern((string) $value);

            if (! $this->caseSensitive && $this->operator === 'like') {
                // Use query builder with case-insensitive comparison
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
