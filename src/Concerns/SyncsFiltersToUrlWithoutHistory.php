<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Livewire\Attributes\Url;

/**
 * Trait for syncing table filters to URL without browser history.
 *
 * Similar to SyncsFiltersToUrl but uses replaceState instead of pushState,
 * preventing URL changes from creating new browser history entries.
 */
trait SyncsFiltersToUrlWithoutHistory
{
    /**
     * Table filters synced to URL (no history).
     */
    #[Url(except: [], history: false, keep: false)]
    public array $tableFilters = [];

    /**
     * Table search synced to URL (no history).
     */
    #[Url(except: '', history: false, keep: false)]
    public ?string $tableSearch = null;

    /**
     * Table sort column synced to URL (no history).
     */
    #[Url(except: '', history: false, keep: false)]
    public ?string $tableSortColumn = null;

    /**
     * Table sort direction synced to URL (no history).
     */
    #[Url(except: '', history: false, keep: false)]
    public ?string $tableSortDirection = null;

    /**
     * Get the current filter query string for manual URL building.
     */
    public function getFilterQueryString(): array
    {
        $query = [];

        if (! empty($this->tableFilters)) {
            $query['tableFilters'] = $this->tableFilters;
        }

        if (! empty($this->tableSearch)) {
            $query['tableSearch'] = $this->tableSearch;
        }

        if (! empty($this->tableSortColumn)) {
            $query['tableSortColumn'] = $this->tableSortColumn;
        }

        if (! empty($this->tableSortDirection)) {
            $query['tableSortDirection'] = $this->tableSortDirection;
        }

        return $query;
    }

    /**
     * Build a shareable URL with current filter state.
     */
    public function getShareableFilterUrl(): string
    {
        $query = $this->getFilterQueryString();

        if (empty($query)) {
            return request()->url();
        }

        return request()->url().'?'.http_build_query($query);
    }

    /**
     * Reset URL parameters when filters are cleared.
     */
    public function resetUrlParameters(): void
    {
        $this->tableFilters = [];
        $this->tableSearch = null;
        $this->tableSortColumn = null;
        $this->tableSortDirection = null;
    }
}
