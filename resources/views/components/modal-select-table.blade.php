<div>
    {{-- Table container --}}
    <div class="overflow-hidden -mx-6">
        {{ $this->table }}
    </div>

    {{-- Bottom action bar --}}
    <div class="mt-4 pt-4 pb-1 border-t border-gray-200 dark:border-gray-700 flex flex-col gap-4">
        {{-- Selection status --}}
        <div class="min-h-7 flex items-center">
            @if(count($selected) > 0)
                <div class="inline-flex items-center gap-2 rounded-lg bg-primary-50 dark:bg-primary-500/10 px-3 py-1.5">
                    <x-filament::badge color="primary">
                        {{ count($selected) }}
                    </x-filament::badge>
                    <span class="text-sm font-medium text-primary-700 dark:text-primary-400">
                        {{ __('filament-dcat-filters::filament-dcat-filters.modal_select.selected') }}
                    </span>
                </div>
            @else
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('filament-dcat-filters::filament-dcat-filters.modal_select.no_items_selected') }}
                </span>
            @endif
        </div>

        {{-- Button group - force single row --}}
        <div class="flex flex-nowrap items-center justify-between gap-4">
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
            <div class="flex flex-nowrap items-center gap-3 shrink-0">
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
    });
</script>
@endpush
