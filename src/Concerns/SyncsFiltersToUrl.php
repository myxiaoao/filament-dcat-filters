<?php

namespace Cooper\FilamentDcatFilters\Concerns;

trait SyncsFiltersToUrl
{
    public array $tableFilters = [];

    public ?string $tableSearch = null;

    public ?string $tableSortColumn = null;

    public ?string $tableSortDirection = null;

    protected bool $urlHistory = true;

    public function withoutUrlHistory(): static
    {
        $this->urlHistory = false;

        return $this;
    }

    public function queryString(): array
    {
        return [
            'tableFilters' => ['except' => [], 'history' => $this->urlHistory, 'keep' => false],
            'tableSearch' => ['except' => '', 'history' => $this->urlHistory, 'keep' => false],
            'tableSortColumn' => ['except' => '', 'history' => $this->urlHistory, 'keep' => false],
            'tableSortDirection' => ['except' => '', 'history' => $this->urlHistory, 'keep' => false],
        ];
    }

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
