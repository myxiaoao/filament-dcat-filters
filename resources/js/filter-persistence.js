/**
 * Filament Dcat Filters - LocalStorage Persistence
 *
 * This script provides client-side filter persistence using the browser's
 * LocalStorage API.
 */

document.addEventListener('livewire:init', () => {
    // Initialize LocalStorage persistence
    Livewire.on('filament-dcat-filters::init-local-storage', ({ key, componentId }) => {
        if (!key || !componentId) return;

        // Prevent duplicate commit hook registration
        if (window.FilamentDcatFilters._commitHookRegistered) return;
        window.FilamentDcatFilters._commitHookRegistered = true;

        // Listen for filter changes and save to LocalStorage
        Livewire.hook('commit', ({ component, respond }) => {
            respond(() => {
                if (component.id !== componentId) return;

                const filters = component.snapshot?.data?.tableFilters?.[0];
                if (filters) {
                    try {
                        localStorage.setItem(key, JSON.stringify(filters));
                    } catch (e) {
                        console.warn('Failed to save filters to LocalStorage:', e);
                    }
                }
            });
        });
    });

    // Restore filters from LocalStorage
    Livewire.on('filament-dcat-filters::restore-from-local-storage', ({ key }) => {
        if (!key) return;

        try {
            const savedFilters = localStorage.getItem(key);
            if (savedFilters) {
                const filters = JSON.parse(savedFilters);
                if (filters !== null && typeof filters === 'object' && !Array.isArray(filters)) {
                    Livewire.dispatch('restoreFiltersFromLocalStorage', { filters });
                }
            }
        } catch (e) {
            console.warn('Failed to restore filters from LocalStorage:', e);
        }
    });

    // Clear filters from LocalStorage
    Livewire.on('filament-dcat-filters::clear-local-storage', ({ key }) => {
        if (!key) return;

        try {
            localStorage.removeItem(key);
        } catch (e) {
            console.warn('Failed to clear filters from LocalStorage:', e);
        }
    });

    // Listen for filter reset to also clear LocalStorage
    Livewire.on('filament-dcat-filters::filters-reset', ({ key }) => {
        if (key) {
            // Clear only the specific component's key
            localStorage.removeItem(key);
        } else {
            // Fallback: clear all filter keys (for backward compatibility)
            const keysToRemove = [];
            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (k && k.startsWith('filament-dcat-filters:')) {
                    keysToRemove.push(k);
                }
            }
            keysToRemove.forEach(k => localStorage.removeItem(k));
        }
    });
});

/**
 * Helper function to manually save filters to LocalStorage
 */
window.FilamentDcatFilters = window.FilamentDcatFilters || {};
window.FilamentDcatFilters.saveFilters = function(key, filters) {
    try {
        localStorage.setItem(key, JSON.stringify(filters));
        return true;
    } catch (e) {
        console.warn('Failed to save filters:', e);
        return false;
    }
};

/**
 * Helper function to manually load filters from LocalStorage
 */
window.FilamentDcatFilters.loadFilters = function(key) {
    try {
        const saved = localStorage.getItem(key);
        return saved ? JSON.parse(saved) : null;
    } catch (e) {
        console.warn('Failed to load filters:', e);
        return null;
    }
};

/**
 * Helper function to clear filters from LocalStorage
 */
window.FilamentDcatFilters.clearFilters = function(key) {
    try {
        if (key) {
            localStorage.removeItem(key);
        } else {
            // Clear all filter keys
            const keysToRemove = [];
            for (let i = 0; i < localStorage.length; i++) {
                const k = localStorage.key(i);
                if (k && k.startsWith('filament-dcat-filters:')) {
                    keysToRemove.push(k);
                }
            }
            keysToRemove.forEach(k => localStorage.removeItem(k));
        }
        return true;
    } catch (e) {
        console.warn('Failed to clear filters:', e);
        return false;
    }
};
