<?php

namespace Cooper\FilamentDcatFilters\Concerns;

trait SyncsFiltersToUrlWithoutHistory
{
    use SyncsFiltersToUrl {
        SyncsFiltersToUrl::queryString as baseQueryString;
    }

    public function queryString(): array
    {
        $this->urlHistory = false;

        return $this->baseQueryString();
    }
}
