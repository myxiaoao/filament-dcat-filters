<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Closure;
use Illuminate\Database\Eloquent\Builder;

/**
 * @requires Classes using this trait must implement getBaseQueryForScopeCounting(): Builder
 */
trait HasScopeBadgeCounts
{
    /** @var array<string, int> */
    protected array $scopeBadgeCounts = [];

    protected bool $showScopeBadgeCounts = true;

    /** @var array<string, array{query?: Closure}> */
    protected array $scopeDefinitions = [];

    public function scopeBadgeCounts(bool $enabled = true): static
    {
        $this->showScopeBadgeCounts = $enabled;

        return $this;
    }

    /** @param array<string, array{query?: Closure}> $scopes */
    public function registerScopesForBadgeCounts(array $scopes): void
    {
        $this->scopeDefinitions = $scopes;
    }

    public function getScopeBadgeCount(string $scopeKey): ?int
    {
        if (! $this->showScopeBadgeCounts) {
            return null;
        }

        if (isset($this->scopeBadgeCounts[$scopeKey])) {
            return $this->scopeBadgeCounts[$scopeKey];
        }

        if (! isset($this->scopeDefinitions[$scopeKey])) {
            return null;
        }

        $count = $this->calculateScopeCount($scopeKey);
        $this->scopeBadgeCounts[$scopeKey] = $count;

        return $count;
    }

    /** @return array<string, int> */
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

    protected function calculateScopeCount(string $scopeKey): int
    {
        $scopeConfig = $this->scopeDefinitions[$scopeKey] ?? null;

        if (! $scopeConfig) {
            return 0;
        }

        $query = clone $this->getBaseQueryForScopeCounting();

        if (isset($scopeConfig['query']) && $scopeConfig['query'] instanceof Closure) {
            $query = $scopeConfig['query']($query);
        }

        return $query->count();
    }

    protected function getBaseQueryForScopeCounting(): Builder
    {
        throw new \RuntimeException(
            'You must override getBaseQueryForScopeCounting() method in your ListRecords class.'
        );
    }

    public function clearScopeBadgeCountCache(): void
    {
        $this->scopeBadgeCounts = [];
    }

    public function refreshScopeBadgeCount(string $scopeKey): ?int
    {
        unset($this->scopeBadgeCounts[$scopeKey]);

        return $this->getScopeBadgeCount($scopeKey);
    }

    public function areScopeBadgeCountsEnabled(): bool
    {
        return $this->showScopeBadgeCounts;
    }

    public function formatScopeBadgeCount(int $count): string
    {
        if ($count >= 1_000_000) {
            return round($count / 1_000_000, 1).'M';
        }

        if ($count >= 1_000) {
            return round($count / 1_000, 1).'K';
        }

        return (string) $count;
    }
}
