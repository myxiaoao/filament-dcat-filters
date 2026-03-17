<?php

use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;

it('resetFiltersAction helper returns ResetFiltersAction', function () {
    $action = resetFiltersAction();
    expect($action)->toBeInstanceOf(ResetFiltersAction::class);
});
