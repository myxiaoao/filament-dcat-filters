<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;
use Filament\Tables\Table;

trait HasResetFilters
{
    /**
     * Get the reset filters action instance.
     */
    protected function getResetFiltersAction(): ResetFiltersAction
    {
        return ResetFiltersAction::make();
    }

    /**
     * Apply the reset filters action to a table.
     */
    protected function withResetFiltersAction(Table $table): Table
    {
        return $table->headerActions([
            $this->getResetFiltersAction(),
            ...$table->getHeaderActions(),
        ]);
    }
}
