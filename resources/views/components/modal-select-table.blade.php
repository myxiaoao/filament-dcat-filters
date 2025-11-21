<div>
    {{-- Table container --}}
    <div class="overflow-hidden -mx-6">
        {{ $this->table }}
    </div>

    {{-- Bottom action bar --}}
    <div style="margin-top: 1rem; padding-top: 1rem; padding-bottom: 0.25rem; border-top: 1px solid rgb(229 231 235); display: flex; flex-direction: column; gap: 1rem;" class="dark:border-gray-700">
        {{-- Selection status --}}
        <div style="min-height: 28px; display: flex; align-items: center;">
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
        <div style="display: flex; flex-wrap: nowrap; align-items: center; justify-content: space-between; gap: 1rem;">
            {{-- Left side --}}
            <div style="flex-shrink: 0;">
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
            <div style="display: flex; flex-wrap: nowrap; align-items: center; gap: 0.75rem; flex-shrink: 0;">
                <x-filament::button
                    color="gray"
                    wire:click="cancel"
                    outlined
                >
                    {{ __('filament-dcat-filters::filament-dcat-filters.modal_select.cancel') }}
                </x-filament::button>

                <x-filament::button
                    wire:click="confirm"
                    :disabled="count($selected) === 0"
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
        // Handle table row click
        Livewire.on('table-row-clicked', (event) => {
            @this.call('selectRow', event.key);
        });
    });
</script>
@endpush
