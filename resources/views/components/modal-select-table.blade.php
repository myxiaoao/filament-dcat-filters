<div>
    {{-- Table container --}}
    <div class="overflow-hidden -mx-6">
        {{ $this->table }}
    </div>

    {{-- Bottom action bar --}}
    <div class="mt-4 pt-4 pb-1 border-t border-gray-200 dark:border-gray-700 flex flex-col gap-4">
        {{-- Selection status with name preview --}}
        <div class="min-h-7 flex items-center">
            @if(count($selected) > 0)
                <div class="inline-flex items-center gap-2 rounded-lg bg-primary-50 dark:bg-primary-500/10 px-3 py-1.5">
                    <x-filament::badge color="primary">
                        {{ count($selected) }}
                    </x-filament::badge>
                    <span class="text-sm font-medium text-primary-700 dark:text-primary-400">
                        {{ __('filament-dcat-filters::filament-dcat-filters.modal_select.selected') }}
                    </span>
                    @php
                        $previewCount = min(2, count($selected));
                        $previewIds = array_slice($selected, 0, $previewCount);
                        $previewLabels = [];
                        if ($modelClass && class_exists($modelClass)) {
                            try {
                                $previewLabels = $modelClass::query()
                                    ->whereIn($keyColumn, $previewIds)
                                    ->pluck($titleColumn)
                                    ->take($previewCount)
                                    ->toArray();
                            } catch (\Throwable) {}
                        }
                        $overflow = count($selected) - $previewCount;
                    @endphp
                    @if(!empty($previewLabels))
                        <span class="text-xs text-primary-600 dark:text-primary-300 truncate max-w-48">
                            ({{ implode(', ', $previewLabels) }}{{ $overflow > 0 ? ' …' : '' }})
                        </span>
                    @endif
                </div>
            @else
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('filament-dcat-filters::filament-dcat-filters.modal_select.no_items_selected') }}
                </span>
            @endif
        </div>

        {{-- Button group - responsive: stacked on mobile, row on sm+ --}}
        <div class="flex flex-col-reverse gap-3 sm:flex-row sm:flex-nowrap sm:items-center sm:justify-between sm:gap-4">
            {{-- Left side --}}
            <div class="shrink-0">
                @if(count($selected) > 0)
                    <x-filament::button
                        wire:click="clearSelection"
                        color="danger"
                        outlined
                        icon="heroicon-m-x-circle"
                    >
                        {{ __('filament-dcat-filters::filament-dcat-filters.modal_select.clear_selection') }}
                    </x-filament::button>
                @endif
            </div>

            {{-- Right side button group --}}
            <div class="flex flex-nowrap items-center gap-3 shrink-0 sm:ml-auto">
                <x-filament::button
                    color="gray"
                    wire:click="cancel"
                    outlined
                >
                    {{ __('filament-dcat-filters::filament-dcat-filters.modal_select.cancel') }}
                </x-filament::button>

                <x-filament::button
                    wire:click="confirm"
                    :disabled="empty($selected)"
                >
                    {{ __('filament-dcat-filters::filament-dcat-filters.modal_select.confirm') }}
                </x-filament::button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('table-row-clicked', (event) => {
            if (event && event.key !== undefined) {
                @this.call('selectRow', event.key);
            }
        });

        // Auto-focus search input when modal opens
        Livewire.on('open-modal', ({ id }) => {
            if (!id || !id.startsWith('modal-select-')) return;
            requestAnimationFrame(() => {
                const modal = document.getElementById(id);
                if (!modal) return;
                const searchInput = modal.querySelector('.fi-ta-search-field input, [wire\\:model\\.live\\.debounce]');
                if (searchInput) searchInput.focus();
            });
        });
    });
</script>
@endpush
