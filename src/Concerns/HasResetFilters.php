<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;
use Filament\Tables\Table;

trait HasResetFilters
{
    /**
     * Boot the trait and automatically add reset filters action.
     */
    public function bootHasResetFilters(): void
    {
        // This method is called automatically by Livewire traits
    }

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
