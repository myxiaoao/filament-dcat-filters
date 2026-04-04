<?php

namespace Cooper\FilamentDcatFilters;

use Composer\InstalledVersions;

class FilamentDcatFilters
{
    /**
     * Get the package version.
     */
    public function version(): string
    {
        return InstalledVersions::getPrettyVersion('cooper/filament-dcat-filters') ?? '0.0.0';
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

    /**
     * Quick access to create a Boolean Filter.
     */
    public function booleanFilter(string $name): Filters\BooleanFilter
    {
        return Filters\BooleanFilter::make($name);
    }

    /**
     * Quick access to create a Null Filter.
     */
    public function nullFilter(string $name): Filters\NullFilter
    {
        return Filters\NullFilter::make($name);
    }

    /**
     * Quick access to create an Enum Filter.
     */
    public function enumFilter(string $name): Filters\EnumFilter
    {
        return Filters\EnumFilter::make($name);
    }

    /**
     * Quick access to create a Full Text Filter.
     */
    public function fullTextFilter(string $name): Filters\FullTextFilter
    {
        return Filters\FullTextFilter::make($name);
    }

    /**
     * Quick access to create a Regex Filter.
     */
    public function regexFilter(string $name): Filters\RegexFilter
    {
        return Filters\RegexFilter::make($name);
    }

    /**
     * Quick access to create a GeoLocation Filter.
     */
    public function geoLocationFilter(string $name): Filters\GeoLocationFilter
    {
        return Filters\GeoLocationFilter::make($name);
    }

    /**
     * Quick access to create a Cascading Select Filter.
     */
    public function cascadingSelectFilter(string $name): Filters\CascadingSelectFilter
    {
        return Filters\CascadingSelectFilter::make($name);
    }

    /**
     * Quick access to create a Relative Date Filter.
     */
    public function relativeDateFilter(string $name): Filters\RelativeDateFilter
    {
        return Filters\RelativeDateFilter::make($name);
    }

    /**
     * Quick access to create an Input Mask Filter.
     */
    public function inputMaskFilter(string $name): Filters\InputMaskFilter
    {
        return Filters\InputMaskFilter::make($name);
    }

    /**
     * Quick access to create a JSON Filter.
     */
    public function jsonFilter(string $name): Filters\JsonFilter
    {
        return Filters\JsonFilter::make($name);
    }

    /**
     * Quick access to create a Find In Set Filter.
     */
    public function findInSetFilter(string $name): Filters\FindInSetFilter
    {
        return Filters\FindInSetFilter::make($name);
    }

    /**
     * Quick access to create a Filter Group.
     */
    public function filterGroup(string $name): Filters\FilterGroup
    {
        return Filters\FilterGroup::make($name);
    }

    /**
     * Quick access to create a Hidden Filter.
     */
    public function hiddenFilter(string $name): Filters\HiddenFilter
    {
        return Filters\HiddenFilter::make($name);
    }

    /**
     * Quick access to create a Soft Delete Filter.
     */
    public function softDeleteFilter(string $name): Filters\SoftDeleteFilter
    {
        return Filters\SoftDeleteFilter::make($name);
    }

    /**
     * Quick access to create an Exists Filter.
     */
    public function existsFilter(string $name): Filters\ExistsFilter
    {
        return Filters\ExistsFilter::make($name);
    }

    /**
     * Quick access to create an Aggregate Filter.
     */
    public function aggregateFilter(string $name): Filters\AggregateFilter
    {
        return Filters\AggregateFilter::make($name);
    }

    /**
     * Quick access to create a Column Compare Filter.
     */
    public function columnCompareFilter(string $name): Filters\ColumnCompareFilter
    {
        return Filters\ColumnCompareFilter::make($name);
    }
}
