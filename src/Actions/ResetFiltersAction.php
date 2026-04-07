<?php

namespace Cooper\FilamentDcatFilters\Actions;

use Closure;
use Filament\Actions\Action;

class ResetFiltersAction extends Action
{
    protected bool $autoHideWhenEmpty = true;

    protected ?Closure $afterResetCallback = null;

    /**
     * Get the default name for this action.
     */
    public static function getDefaultName(): ?string
    {
        return 'resetFilters';
    }

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->label(__('filament-dcat-filters::filament-dcat-filters.reset_filters.label'));

        $this->icon('heroicon-o-x-mark');

        $this->color('gray');

        $this->size('sm');

        $this->action(function ($livewire): void {
            $this->resetFilters($livewire);
        });

        $this->visible(function ($livewire): bool {
            if (! $this->autoHideWhenEmpty) {
                return true;
            }

            return $this->hasActiveFilters($livewire);
        });
    }

    /**
     * Enable confirmation modal before resetting.
     */
    public function withConfirmation(): static
    {
        $this->requiresConfirmation();
        $this->modalHeading(__('filament-dcat-filters::filament-dcat-filters.reset_filters.confirm_heading'));
        $this->modalDescription(__('filament-dcat-filters::filament-dcat-filters.reset_filters.confirm_description'));
        $this->modalSubmitActionLabel(__('filament-dcat-filters::filament-dcat-filters.reset_filters.confirm_button'));

        return $this;
    }

    /**
     * Configure whether to auto-hide when no filters are active.
     */
    public function autoHideWhenEmpty(bool $autoHide = true): static
    {
        $this->autoHideWhenEmpty = $autoHide;

        return $this;
    }

    /**
     * Set a callback to run after filters are reset.
     */
    public function afterReset(?Closure $callback): static
    {
        $this->afterResetCallback = $callback;

        return $this;
    }

    /**
     * Reset all filters on the table.
     */
    protected function resetFilters(mixed $livewire): void
    {
        if (! property_exists($livewire, 'tableFilters')) {
            return;
        }

        $livewire->tableFilters = [];

        if (method_exists($livewire, 'resetTable')) {
            $livewire->resetTable();
        }

        if ($this->afterResetCallback instanceof Closure) {
            ($this->afterResetCallback)($livewire);
        }

        // Pass the component-specific key so only this component's LocalStorage is cleared
        $key = $this->resolveLocalStorageKey($livewire);

        $livewire->dispatch('filament-dcat-filters::filters-reset', key: $key);
    }

    /**
     * Resolve the LocalStorage key for the current component.
     */
    protected function resolveLocalStorageKey(mixed $livewire): ?string
    {
        if (method_exists($livewire, 'getLocalStorageKey')) {
            return $livewire->getLocalStorageKey();
        }

        return null;
    }

    /**
     * Check if there are any active filters.
     */
    protected function hasActiveFilters(mixed $livewire): bool
    {
        if (! property_exists($livewire, 'tableFilters')) {
            return false;
        }

        $filters = $livewire->tableFilters ?? [];

        if (! is_array($filters)) {
            return false;
        }

        return $this->hasNonEmptyValues($filters);
    }

    /**
     * Recursively check if an array has any non-empty values.
     */
    protected function hasNonEmptyValues(array $array): bool
    {
        foreach ($array as $value) {
            if (is_array($value)) {
                if ($this->hasNonEmptyValues($value)) {
                    return true;
                }
                /** @phpstan-ignore notIdentical.alwaysTrue (defensive check — $value is already narrowed to non-array) */
            } elseif ($value !== null && $value !== '' && $value !== []) {
                return true;
            }
        }

        return false;
    }
}
