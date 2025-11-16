<?php

namespace Cooper\FilamentDcatFilters\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Cooper\FilamentDcatFilters\FilamentDcatFilters
 *
 * @method static string version()
 * @method static mixed config(?string $key = null, mixed $default = null)
 * @method static \Cooper\FilamentDcatFilters\Filters\ScopeFilter scopeFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\RangeFilter rangeFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\LikeFilter likeFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\InFilter inFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\BetweenFilter betweenFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\ComparisonFilter comparisonFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\DateComponentFilter dateComponentFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\SelectTableFilter selectTableFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\ModalSelectFilter modalSelectFilter(string $name)
 */
class FilamentDcatFilters extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return \Cooper\FilamentDcatFilters\FilamentDcatFilters::class;
    }
}
