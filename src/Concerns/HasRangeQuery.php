<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait HasRangeQuery
{
    /**
     * Apply range query to the builder.
     */
    protected function applyRangeQuery(Builder $query, string $column, array $data): Builder
    {
        $from = $data['from'] ?? null;
        $to = $data['to'] ?? null;

        // Both values are empty
        if (empty($from) && empty($to)) {
            return $query;
        }

        // Only 'to' value is provided
        if (empty($from) && ! empty($to)) {
            return $query->where($column, '<=', $to);
        }

        // Only 'from' value is provided
        if (! empty($from) && empty($to)) {
            return $query->where($column, '>=', $from);
        }

        // Both values are provided
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
