<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasRelationship
{
    protected ?string $relationshipName = null;

    protected ?string $relationshipTitleColumn = null;

    /**
     * Set the relationship to query through.
     */
    public function relationship(string $name, ?string $titleColumn = null): static
    {
        $this->relationshipName = $name;
        $this->relationshipTitleColumn = $titleColumn;

        return $this;
    }

    /**
     * Check if a relationship has been configured.
     */
    public function hasRelationship(): bool
    {
        return $this->relationshipName !== null;
    }

    /**
     * Apply a single-value constraint through a relationship using whereHas.
     */
    protected function applyRelationshipConstraint(Builder $query, string $column, string $operator, mixed $value): Builder
    {
        return $query->whereHas($this->relationshipName, function (Builder $q) use ($column, $operator, $value) {
            $q->where($column, $operator, $value);
        });
    }

    /**
     * Apply a multi-value constraint through a relationship using whereHas + whereIn/whereNotIn.
     */
    protected function applyRelationshipWhereIn(Builder $query, string $column, array $values, bool $negate = false): Builder
    {
        return $query->whereHas($this->relationshipName, function (Builder $q) use ($column, $values, $negate) {
            if ($negate) {
                $q->whereNotIn($column, $values);
            } else {
                $q->whereIn($column, $values);
            }
        });
    }
}
