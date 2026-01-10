<?php

use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;

if (! function_exists('resetFiltersAction')) {
    /**
     * Create a new ResetFiltersAction instance.
     */
    function resetFiltersAction(): ResetFiltersAction
    {
        return ResetFiltersAction::make();
    }
}
