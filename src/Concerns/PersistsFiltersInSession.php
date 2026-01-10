<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Illuminate\Support\Facades\Session;

/**
 * Trait for persisting table filters in session storage.
 *
 * This trait provides server-side session persistence for table filters,
 * ensuring filter state survives page refreshes within the same session.
 */
trait PersistsFiltersInSession
{
    /**
     * Get the session key for storing filters.
     */
    protected function getFilterSessionKey(): string
    {
        return 'filament-dcat-filters:'.static::class;
    }

    /**
     * Boot the trait and restore filters from session.
     */
    public function bootPersistsFiltersInSession(): void
    {
        $this->restoreFiltersFromSession();
    }

    /**
     * Restore filters from session storage.
     */
    protected function restoreFiltersFromSession(): void
    {
        if (! property_exists($this, 'tableFilters')) {
            return;
        }

        $savedFilters = Session::get($this->getFilterSessionKey());

        if (is_array($savedFilters) && ! empty($savedFilters)) {
            $this->tableFilters = $savedFilters;
        }
    }

    /**
     * Save current filters to session storage.
     */
    protected function saveFiltersToSession(): void
    {
        if (! property_exists($this, 'tableFilters')) {
            return;
        }

        Session::put($this->getFilterSessionKey(), $this->tableFilters);
    }

    /**
     * Clear filters from session storage.
     */
    protected function clearFiltersFromSession(): void
    {
        Session::forget($this->getFilterSessionKey());
    }

    /**
     * Hook into Livewire's updatedTableFilters to persist changes.
     */
    public function updatedTableFilters(): void
    {
        $this->saveFiltersToSession();
    }
}
