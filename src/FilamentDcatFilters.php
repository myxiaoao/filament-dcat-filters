<?php

namespace Cooper\FilamentDcatFilters;

class FilamentDcatFilters
{
    /**
     * Get the package version.
     */
    public function version(): string
    {
        return '1.0.0';
    }

    /**
     * Get the package configuration.
     */
    public function config(?string $key = null, mixed $default = null): mixed
    {
        if ($key === null) {
            return config('filament-dcat-filters');
        }

        return config("filament-dcat-filters.{$key}", $default);
    }

    /**
     * Quick access to create a Scope Filter.
     */
    public function scopeFilter(string $name): Filters\ScopeFilter
    {
        return Filters\ScopeFilter::make($name);
    }

    /**
     * Quick access to create a Range Filter.
     */
    public function rangeFilter(string $name): Filters\RangeFilter
    {
        return Filters\RangeFilter::make($name);
    }

    /**
     * Quick access to create a Like Filter.
     */
    public function likeFilter(string $name): Filters\LikeFilter
    {
        return Filters\LikeFilter::make($name);
    }

    /**
     * Quick access to create an In Filter.
     */
    public function inFilter(string $name): Filters\InFilter
    {
        return Filters\InFilter::make($name);
    }

    /**
     * Quick access to create a Between Filter.
     */
    public function betweenFilter(string $name): Filters\BetweenFilter
    {
        return Filters\BetweenFilter::make($name);
    }

    /**
     * Quick access to create a Comparison Filter.
     */
    public function comparisonFilter(string $name): Filters\ComparisonFilter
    {
        return Filters\ComparisonFilter::make($name);
    }

    /**
     * Quick access to create a Date Component Filter.
     */
    public function dateComponentFilter(string $name): Filters\DateComponentFilter
    {
        return Filters\DateComponentFilter::make($name);
    }

    /**
     * Quick access to create a SelectTable Filter.
     */
    public function selectTableFilter(string $name): Filters\SelectTableFilter
    {
        return Filters\SelectTableFilter::make($name);
    }

    /**
     * Quick access to create a Modal Select Filter (Dcat Admin style).
     */
    public function modalSelectFilter(string $name): Filters\ModalSelectFilter
    {
        return Filters\ModalSelectFilter::make($name);
    }
}
