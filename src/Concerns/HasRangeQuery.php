<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasRangeQuery
{
    /**
     * Check if a value is considered empty for range filtering.
     * Unlike empty(), this treats "0" as a valid non-empty value.
     */
    protected function isRangeValueEmpty(mixed $value): bool
    {
        return $value === null || $value === '';
    }

    /**
     * Apply range query to the builder.
     */
    protected function applyRangeQuery(Builder $query, string $column, array $data): Builder
    {
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;

        $fromEmpty = $this->isRangeValueEmpty($from);
        $toEmpty = $this->isRangeValueEmpty($to);

        // Both values are empty
        if ($fromEmpty && $toEmpty) {
            return $query;
        }

        // Only 'to' value is provided
        if ($fromEmpty && ! $toEmpty) {
            return $query->where($column, '<=', $to);
        }

        // Only 'from' value is provided
        if (! $fromEmpty && $toEmpty) {
            return $query->where($column, '>=', $from);
        }

        // Both values are provided - swap if from > to
        if ($from > $to) {
            [$from, $to] = [$to, $from];
        }

        return $query->whereBetween($column, [$from, $to]);
    }

    /**
     * Generate indicators for active range filters.
     */
    protected function generateRangeIndicators(array $data, string $label): array
    {
        $indicators = [];
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;

        if ($from) {
            $indicators[] = "{$label} from {$from}";
        }

        if ($to) {
            $indicators[] = "{$label} to {$to}";
        }

        return $indicators;
    }
}
