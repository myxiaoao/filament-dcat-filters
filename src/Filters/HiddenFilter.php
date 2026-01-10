<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\Hidden;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class HiddenFilter extends Filter
{
    protected const ALLOWED_OPERATORS = ['=', '!=', '>', '>=', '<', '<=', 'like', 'not like'];

    protected mixed $defaultValue = null;

    protected string $operator = '=';

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);

        // Configure hidden input
        $this->form([
            Hidden::make('value')
                ->default($this->defaultValue),
        ]);

        $this->configureQuery();
    }

    /**
     * Set the default value for this filter.
     */
    public function default(mixed $state = true): static
    {
        $this->defaultValue = $state;

        $this->form([
            Hidden::make('value')
                ->default($this->defaultValue),
        ]);

        return $this;
    }

    /**
     * Set the comparison operator with validation.
     */
    public function operator(string $operator): static
    {
        if (! in_array($operator, self::ALLOWED_OPERATORS, true)) {
            throw new \InvalidArgumentException(
                "Invalid operator: {$operator}. Allowed: ".implode(', ', self::ALLOWED_OPERATORS)
            );
        }

        $this->operator = $operator;
        $this->configureQuery();

        return $this;
    }

    /**
     * Set operator to greater than (>).
     */
    public function gt(): static
    {
        return $this->operator('>');
    }

    /**
     * Set operator to greater than or equal (>=).
     */
    public function gte(): static
    {
        return $this->operator('>=');
    }

    /**
     * Set operator to less than (<).
     */
    public function lt(): static
    {
        return $this->operator('<');
    }

    /**
     * Set operator to less than or equal (<=).
     */
    public function lte(): static
    {
        return $this->operator('<=');
    }

    /**
     * Set operator to equal (=).
     */
    public function eq(): static
    {
        return $this->operator('=');
    }

    /**
     * Set operator to not equal (!=).
     */
    public function ne(): static
    {
        return $this->operator('!=');
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? $this->defaultValue;

            if ($value === null || $value === '') {
                return $query;
            }

            $column = $this->getName();

            // Validate operator at query time as well
            if (! in_array($this->operator, self::ALLOWED_OPERATORS, true)) {
                return $query;
            }

            return $query->where($column, $this->operator, $value);
        });

        // Hidden filters don't show indicators
        $this->indicateUsing(fn () => []);
    }
}
