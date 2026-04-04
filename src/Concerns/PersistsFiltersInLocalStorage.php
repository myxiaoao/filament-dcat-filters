<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Livewire\Attributes\On;

/**
 * Trait for enabling client-side filter persistence via LocalStorage.
 *
 * This trait adds the necessary JavaScript to persist table filters
 * in the browser's LocalStorage, surviving session expiry.
 */
trait PersistsFiltersInLocalStorage
{
    /**
     * Get the LocalStorage key for storing filters.
     */
    public function getLocalStorageKey(): string
    {
        return 'filament-dcat-filters:'.str_replace('\\', '_', static::class);
    }

    /**
     * Initialize the LocalStorage persistence script.
     *
     * Call this method in your Livewire component's render or mount method
     * to inject the persistence JavaScript.
     */
    protected function initLocalStoragePersistence(): void
    {
        $this->dispatch('filament-dcat-filters::init-local-storage', [
            'key' => $this->getLocalStorageKey(),
            'componentId' => $this->getId(),
        ]);
    }

    /**
     * Mount hook for LocalStorage persistence.
     */
    public function mountPersistsFiltersInLocalStorage(): void
    {
        // Dispatch event to restore filters from LocalStorage
        $this->dispatch('filament-dcat-filters::restore-from-local-storage', [
            'key' => $this->getLocalStorageKey(),
        ]);
    }

    /**
     * Receive filters restored from LocalStorage.
     */
    #[On('restoreFiltersFromLocalStorage')]
    public function restoreFiltersFromLocalStorage(array $filters): void
    {
        if (! property_exists($this, 'tableFilters')) {
            return;
        }

        if (! empty($filters)) {
            $this->tableFilters = $filters;
        }
    }
}
