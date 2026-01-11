<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;

trait HasScopeBadgeCounts
{
    /**
     * Cache for scope counts.
     *
     * @var array<string, int>
     */
    protected array $scopeBadgeCounts = [];

    /**
     * Whether to show badge counts on scope tabs.
     */
    protected bool $showScopeBadgeCounts = true;

    /**
     * The scope definitions for counting.
     *
     * @var array<string, array{query?: Closure}>
     */
    protected array $scopeDefinitions = [];

    /**
     * Enable or disable scope badge counts.
     */
    public function scopeBadgeCounts(bool $enabled = true): static
    {
        $this->showScopeBadgeCounts = $enabled;

        return $this;
    }

    /**
     * Register scope definitions for badge counting.
     *
     * @param  array<string, array{query?: Closure}>  $scopes
     */
    public function registerScopesForBadgeCounts(array $scopes): void
    {
        $this->scopeDefinitions = $scopes;
    }

    /**
     * Get the count for a specific scope.
     */
    public function getScopeBadgeCount(string $scopeKey): ?int
    {
        if (! $this->showScopeBadgeCounts) {
            return null;
        }

        // Return cached count if available
        if (isset($this->scopeBadgeCounts[$scopeKey])) {
            return $this->scopeBadgeCounts[$scopeKey];
        }

        // Calculate count if scope definition exists
        if (isset($this->scopeDefinitions[$scopeKey])) {
            $count = $this->calculateScopeCount($scopeKey);
            $this->scopeBadgeCounts[$scopeKey] = $count;

            return $count;
        }

        return null;
    }

    /**
     * Get all scope badge counts.
     *
     * @return array<string, int>
     */
    public function getAllScopeBadgeCounts(): array
    {
        if (! $this->showScopeBadgeCounts) {
            return [];
        }

        $counts = [];

        foreach (array_keys($this->scopeDefinitions) as $scopeKey) {
            $counts[$scopeKey] = $this->getScopeBadgeCount($scopeKey);
        }

        return $counts;
    }

    /**
     * Calculate the count for a scope.
     */
    protected function calculateScopeCount(string $scopeKey): int
    {
        $baseQuery = $this->getBaseQueryForScopeCounting();

        $scopeConfig = $this->scopeDefinitions[$scopeKey] ?? null;

        if (! $scopeConfig) {
            return 0;
        }

        // Clone the query to avoid modifying the original
        $query = clone $baseQuery;

        // Apply scope query if defined
        if (isset($scopeConfig['query']) && $scopeConfig['query'] instanceof Closure) {
            $query = $scopeConfig['query']($query);
        }

        return $query->count();
    }

    /**
     * Get the base query for scope counting.
     * Override this method in your ListRecords class to provide the base query.
     */
    protected function getBaseQueryForScopeCounting(): Builder
    {
        // Default implementation - override in your class
        // Example: return $this->getTableQuery()->getModel()->newQuery();
        throw new \RuntimeException(
            'You must override getBaseQueryForScopeCounting() method in your ListRecords class.'
        );
    }

    /**
     * Clear the scope badge count cache.
     */
    public function clearScopeBadgeCountCache(): void
    {
        $this->scopeBadgeCounts = [];
    }

    /**
     * Refresh a specific scope's badge count.
     */
    public function refreshScopeBadgeCount(string $scopeKey): ?int
    {
        unset($this->scopeBadgeCounts[$scopeKey]);

        return $this->getScopeBadgeCount($scopeKey);
    }

    /**
     * Check if scope badge counts are enabled.
     */
    public function areScopeBadgeCountsEnabled(): bool
    {
        return $this->showScopeBadgeCounts;
    }

    /**
     * Format a count for display (e.g., 1000 -> 1K).
     */
    public function formatScopeBadgeCount(int $count): string
    {
        if ($count >= 1000000) {
            return round($count / 1000000, 1) . 'M';
        }

        if ($count >= 1000) {
            return round($count / 1000, 1) . 'K';
        }

        return (string) $count;
    }
}
