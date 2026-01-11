<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

trait HasFilterPresets
{
    /**
     * Get the filter presets for this table.
     * Override this method in your ListRecords class.
     *
     * @return array<string, array{label: string, filters: array, icon?: string, color?: string}>
     */
    protected function getFilterPresets(): array
    {
        return [];
    }

    /**
     * Get actions for filter presets to be displayed in the table header.
     *
     * @return array<Action>
     */
    protected function getFilterPresetActions(): array
    {
        $presets = $this->getFilterPresets();

        if (empty($presets)) {
            return [];
        }

        $actions = [];

        foreach ($presets as $key => $preset) {
            $actions[] = Action::make("preset_{$key}")
                ->label($preset['label'])
                ->icon($preset['icon'] ?? Heroicon::Funnel)
                ->color($preset['color'] ?? 'gray')
                ->size('sm')
                ->action(function () use ($preset) {
                    $this->applyFilterPreset($preset['filters']);
                });
        }

        return $actions;
    }

    /**
     * Apply a filter preset to the table.
     */
    protected function applyFilterPreset(array $filters): void
    {
        $tableFilters = [];

        foreach ($filters as $filterName => $value) {
            if (is_array($value)) {
                $tableFilters[$filterName] = $value;
            } else {
                $tableFilters[$filterName] = ['value' => $value];
            }
        }

        $this->tableFilters = $tableFilters;

        // Reset pagination when applying preset
        $this->resetPage();
    }

    /**
     * Reset all filter presets and clear filters.
     */
    protected function resetFilterPresets(): void
    {
        $this->tableFilters = [];
        $this->resetPage();
    }

    /**
     * Check if a specific preset is currently active.
     */
    protected function isFilterPresetActive(string $presetKey): bool
    {
        $presets = $this->getFilterPresets();

        if (! isset($presets[$presetKey])) {
            return false;
        }

        $presetFilters = $presets[$presetKey]['filters'];
        $currentFilters = $this->tableFilters ?? [];

        foreach ($presetFilters as $filterName => $value) {
            $currentValue = $currentFilters[$filterName] ?? null;

            if (is_array($value)) {
                if ($currentValue !== $value) {
                    return false;
                }
            } else {
                if (($currentValue['value'] ?? null) !== $value) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the currently active preset key, if any.
     */
    protected function getActiveFilterPreset(): ?string
    {
        $presets = $this->getFilterPresets();

        foreach (array_keys($presets) as $key) {
            if ($this->isFilterPresetActive($key)) {
                return $key;
            }
        }

        return null;
    }
}
