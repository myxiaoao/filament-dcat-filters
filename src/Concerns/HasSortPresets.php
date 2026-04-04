<?php

namespace Cooper\FilamentDcatFilters\Concerns;

/**
 * Provides sort preset functionality for Filament ListRecords pages.
 *
 * Usage in a ListRecords class:
 *
 *     use HasSortPresets;
 *
 *     protected function getSortPresets(): array
 *     {
 *         return [
 *             'newest' => ['column' => 'created_at', 'direction' => 'desc', 'label' => 'Newest'],
 *             'oldest' => ['column' => 'created_at', 'direction' => 'asc', 'label' => 'Oldest'],
 *             'price_high' => ['column' => 'price', 'direction' => 'desc', 'label' => 'Price ↓'],
 *             'name_asc' => [
 *                 'columns' => [
 *                     ['column' => 'last_name', 'direction' => 'asc'],
 *                     ['column' => 'first_name', 'direction' => 'asc'],
 *                 ],
 *                 'label' => 'Name A-Z',
 *             ],
 *         ];
 *     }
 */
trait HasSortPresets
{
    protected ?string $activeSortPreset = null;

    /**
     * Define sort presets. Override in the using class.
     *
     * Each preset is an array with:
     * - Single column: ['column' => string, 'direction' => 'asc'|'desc', 'label' => string]
     * - Multi column:  ['columns' => [['column' => string, 'direction' => string], ...], 'label' => string]
     *
     * @return array<string, array>
     */
    protected function getSortPresets(): array
    {
        return [];
    }

    /**
     * Apply a sort preset by key.
     */
    public function applySortPreset(string $key): void
    {
        $presets = $this->getSortPresets();

        if (! isset($presets[$key])) {
            return;
        }

        $preset = $presets[$key];
        $this->activeSortPreset = $key;

        if (isset($preset['columns'])) {
            // Multi-column sort: apply first column as primary sort via Filament's built-in
            $first = $preset['columns'][0] ?? null;

            if ($first) {
                $this->tableSortColumn = $first['column'];
                $this->tableSortDirection = $first['direction'] ?? 'asc';
            }

            return;
        }

        // Single column sort
        if (isset($preset['column'])) {
            $this->tableSortColumn = $preset['column'];
            $this->tableSortDirection = $preset['direction'] ?? 'asc';
        }
    }

    /**
     * Reset sort to default (no preset active).
     */
    public function resetSortPreset(): void
    {
        $this->activeSortPreset = null;
        $this->tableSortColumn = null;
        $this->tableSortDirection = null;
    }

    /**
     * Get the currently active sort preset key.
     */
    public function getActiveSortPreset(): ?string
    {
        return $this->activeSortPreset;
    }

    /**
     * Check if a specific sort preset is active.
     */
    public function isSortPresetActive(string $key): bool
    {
        return $this->activeSortPreset === $key;
    }

    /**
     * Get sort preset options formatted for UI (e.g., Select component).
     *
     * @return array<string, string> ['key' => 'label', ...]
     */
    public function getSortPresetOptions(): array
    {
        $options = [];

        foreach ($this->getSortPresets() as $key => $preset) {
            $options[$key] = $preset['label'] ?? ucfirst(str_replace('_', ' ', $key));
        }

        return $options;
    }

    /**
     * Get Filament Actions for sort presets (for use in header/toolbar).
     *
     * @return array<\Filament\Actions\Action>
     */
    public function getSortPresetActions(): array
    {
        $actions = [];

        foreach ($this->getSortPresets() as $key => $preset) {
            $label = $preset['label'] ?? ucfirst(str_replace('_', ' ', $key));

            $actions[] = \Filament\Actions\Action::make("sort_preset_{$key}")
                ->label($label)
                ->action(fn () => $this->applySortPreset($key))
                ->color($this->isSortPresetActive($key) ? 'primary' : 'gray')
                ->size('sm');
        }

        return $actions;
    }

    /**
     * Export current sort state as a shareable array.
     *
     * @return array{preset: ?string, column: ?string, direction: ?string}
     */
    public function exportSortState(): array
    {
        return [
            'preset' => $this->activeSortPreset,
            'column' => $this->tableSortColumn ?? null,
            'direction' => $this->tableSortDirection ?? null,
        ];
    }

    /**
     * Import sort state from a shared array.
     */
    public function importSortState(array $state): void
    {
        if (isset($state['preset']) && $state['preset'] !== null) {
            $this->applySortPreset($state['preset']);

            return;
        }

        $this->activeSortPreset = null;
        $this->tableSortColumn = $state['column'] ?? null;
        $this->tableSortDirection = $state['direction'] ?? null;
    }
}
