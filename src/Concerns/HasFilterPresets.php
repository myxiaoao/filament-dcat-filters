<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Filament\Actions\Action;
use Filament\Support\Icons\Heroicon;

trait HasFilterPresets
{
    /** @return array<string, array{label: string, filters: array, icon?: string, color?: string}> */
    protected function getFilterPresets(): array
    {
        return [];
    }

    /** @return array<Action> */
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
                ->action(fn () => $this->applyFilterPreset($preset['filters']));
        }

        return $actions;
    }

    protected function applyFilterPreset(array $filters): void
    {
        $tableFilters = [];

        foreach ($filters as $filterName => $value) {
            $tableFilters[$filterName] = is_array($value) ? $value : ['value' => $value];
        }

        $this->tableFilters = $tableFilters;
        $this->resetPage();
    }

    protected function resetFilterPresets(): void
    {
        $this->tableFilters = [];
        $this->resetPage();
    }

    protected function isFilterPresetActive(string $presetKey): bool
    {
        $presets = $this->getFilterPresets();

        if (! isset($presets[$presetKey])) {
            return false;
        }

        $currentFilters = $this->tableFilters ?? [];

        foreach ($presets[$presetKey]['filters'] as $filterName => $expectedValue) {
            $currentValue = $currentFilters[$filterName] ?? null;

            if (is_array($expectedValue)) {
                if ($currentValue !== $expectedValue) {
                    return false;
                }
            } elseif (($currentValue['value'] ?? null) !== $expectedValue) {
                return false;
            }
        }

        return true;
    }

    protected function getActiveFilterPreset(): ?string
    {
        foreach (array_keys($this->getFilterPresets()) as $key) {
            if ($this->isFilterPresetActive($key)) {
                return $key;
            }
        }

        return null;
    }
}
