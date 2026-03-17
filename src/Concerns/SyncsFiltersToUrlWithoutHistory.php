<?php

namespace Cooper\FilamentDcatFilters\Concerns;

trait SyncsFiltersToUrlWithoutHistory
{
    use SyncsFiltersToUrl;

    protected function initSyncsFiltersToUrlWithoutHistory(): void
    {
        $this->urlHistory = false;
    }

    public function queryString(): array
    {
        $this->urlHistory = false;

        return [
            'tableFilters' => ['except' => [], 'history' => false, 'keep' => false],
            'tableSearch' => ['except' => '', 'history' => false, 'keep' => false],
            'tableSortColumn' => ['except' => '', 'history' => false, 'keep' => false],
            'tableSortDirection' => ['except' => '', 'history' => false, 'keep' => false],
        ];
    }
}
