<?php

namespace Cooper\FilamentDcatFilters\Concerns;

/**
 * @requires Classes using this trait should implement configureQuery(): void
 */
trait HasOperator
{
    protected string $operator = '=';

    /**
     * Set a custom operator with validation.
     */
    public function operator(string $operator): static
    {
        if (defined('static::ALLOWED_OPERATORS')) {
            if (! in_array($operator, static::ALLOWED_OPERATORS, true)) {
                throw new \InvalidArgumentException(
                    "Invalid operator: {$operator}. Allowed: ".implode(', ', static::ALLOWED_OPERATORS)
                );
            }
        }

        $this->operator = $operator;

        /** @phpstan-ignore function.alreadyNarrowedType (defensive check — trait may be used without configureQuery) */
        if (method_exists($this, 'configureQuery')) {
            $this->configureQuery();
        }

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
}
