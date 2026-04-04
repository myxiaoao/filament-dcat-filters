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
 * @method static \Cooper\FilamentDcatFilters\Filters\BooleanFilter booleanFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\NullFilter nullFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\EnumFilter enumFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\FullTextFilter fullTextFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\RegexFilter regexFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\GeoLocationFilter geoLocationFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\CascadingSelectFilter cascadingSelectFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\RelativeDateFilter relativeDateFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\InputMaskFilter inputMaskFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\JsonFilter jsonFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\FindInSetFilter findInSetFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\FilterGroup filterGroup(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\HiddenFilter hiddenFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\SoftDeleteFilter softDeleteFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\ExistsFilter existsFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\AggregateFilter aggregateFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\ColumnCompareFilter columnCompareFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\AdvancedJsonFilter advancedJsonFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\TimezoneAwareDateFilter timezoneAwareDateFilter(string $name)
 * @method static \Cooper\FilamentDcatFilters\Filters\MorphRelationFilter morphRelationFilter(string $name)
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
