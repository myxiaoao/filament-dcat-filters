<?php

namespace Cooper\FilamentDcatFilters\Concerns;

trait HasColumnName
{
    protected ?string $columnName = null;

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
     * Resolve the actual column name to use in queries.
     */
    protected function resolveColumnName(): string
    {
        return $this->columnName ?? $this->getName();
    }
}
