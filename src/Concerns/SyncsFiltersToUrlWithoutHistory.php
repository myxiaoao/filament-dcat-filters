<?php

namespace Cooper\FilamentDcatFilters\Concerns;

trait SyncsFiltersToUrlWithoutHistory
{
    use SyncsFiltersToUrl;

    public function queryString(): array
    {
        return [
            'tableFilters' => ['except' => [], 'history' => false, 'keep' => false],
            'tableSearch' => ['except' => '', 'history' => false, 'keep' => false],
            'tableSortColumn' => ['except' => '', 'history' => false, 'keep' => false],
            'tableSortDirection' => ['except' => '', 'history' => false, 'keep' => false],
        ];
    }
}
