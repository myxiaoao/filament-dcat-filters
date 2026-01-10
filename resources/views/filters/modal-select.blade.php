@php
    $modalId = 'modal-select-' . $filterName;
    $placeholder = $multiple
        ? __('filament-dcat-filters::filament-dcat-filters.modal_select.placeholder_multiple')
        : __('filament-dcat-filters::filament-dcat-filters.modal_select.placeholder_single');
@endphp

<div class="fi-fo-field-wrp">
    {{-- Field Label --}}
    @if($label)
        <div class="fi-fo-field-wrp-label-ctn flex items-center gap-x-3 justify-between">
            <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
                <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                    {{ $label }}
                </span>
            </label>
        </div>
    @endif

<div
    x-data="{
        selected: [],
        selectedLabels: [],
        open: false,
        loading: false,
        error: null,
        triggerElement: null,

        init() {
            // Listen for selection confirmation event (Livewire 3 syntax)
            window.addEventListener('modal-select-confirmed', (event) => {
                const detail = event.detail;

                if (detail.filterKey === '{{ $filterName }}') {
                    this.updateSelection(detail.selected, detail.modelClass, detail.titleColumn, detail.keyColumn);
                    this.announceToScreenReader('{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.selection_updated') }}');
                }
            });
        },

        openModal(event) {
            // Prevent default behavior of disabled select
            if (event) {
                event.preventDefault();
                event.stopPropagation();
                this.triggerElement = event.target;
            }
            this.open = true;
            this.error = null;
            $dispatch('open-modal', { id: '{{ $modalId }}' });
        },

        handleKeydown(event) {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                this.openModal(event);
            }
        },

        announceToScreenReader(message) {
            const announcement = document.createElement('div');
            announcement.setAttribute('role', 'status');
            announcement.setAttribute('aria-live', 'polite');
            announcement.setAttribute('aria-atomic', 'true');
            announcement.className = 'sr-only';
            announcement.textContent = message;
            document.body.appendChild(announcement);
            setTimeout(() => announcement.remove(), 1000);
        },

        updateSelection(selected, modelClass, titleColumn, keyColumn) {
            this.selected = Array.isArray(selected) ? selected : [selected];
            this.error = null;

            // Fetch selected item labels via Livewire
            @if($modelClass)
                this.loading = true;
                fetch('{{ route('filament-dcat-filters.fetch-labels') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        model: modelClass || '{{ $modelClass }}',
                        ids: this.selected,
                        column: titleColumn || '{{ $titleColumn }}',
                        keyColumn: keyColumn || '{{ $keyColumn }}'
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('{{ __('filament-dcat-filters::filament-dcat-filters.modal_select.fetch_error') }}');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        this.error = data.error;
                        this.selectedLabels = [];
                    } else {
                        this.selectedLabels = data.labels || [];
                    }
                    this.updateHiddenInput();
                })
                .catch(error => {
                    console.error('Failed to fetch labels:', error);
                    this.error = error.message || '{{ __('filament-dcat-filters::filament-dcat-filters.modal_select.fetch_error') }}';
                    this.selectedLabels = this.selected.map(id => '#' + id);
                    this.updateHiddenInput();
                })
                .finally(() => {
                    this.loading = false;
                });
            @endif
        },

        updateHiddenInput() {
            const hiddenInput = this.$refs.hiddenInput;
            if (hiddenInput) {
                hiddenInput.value = this.selected.join(',');
                hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        },

        removeItem(index) {
            this.selected.splice(index, 1);
            this.selectedLabels.splice(index, 1);
            this.updateHiddenInput();
        },

        clear() {
            this.selected = [];
            this.selectedLabels = [];
            this.error = null;
            this.updateHiddenInput();
            this.announceToScreenReader('{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.selection_cleared') }}');
        }
    }"
    role="combobox"
    aria-haspopup="dialog"
    :aria-expanded="open"
    aria-label="{{ $label ?? __('filament-dcat-filters::filament-dcat-filters.modal_select.default_title') }}"
>
    <div class="flex items-center gap-x-3 justify-between">
            {{-- Select input wrapper --}}
            <div class="fi-input-wrp fi-fo-select flex rounded-lg shadow-sm ring-1 transition duration-75 bg-white dark:bg-white/5 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-2 ring-gray-950/10 dark:ring-white/20 [&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-600 dark:[&:not(:has(.fi-ac-action:focus))]:focus-within:ring-primary-500 min-w-0 flex-1 relative">
                {{-- Transparent click layer with keyboard support --}}
                <div
                    @click="openModal($event)"
                    @keydown="handleKeydown($event)"
                    tabindex="0"
                    role="button"
                    :aria-label="selectedLabels.length > 0 ? '{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.change_selection') }}: ' + selectedLabels.join(', ') : '{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.open_selection') }}'"
                    class="absolute inset-0 z-10 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-lg"
                ></div>

                <div class="fi-input-wrp-content-ctn">
                    <div class="min-w-0 flex-1">
                        <div class="fi-select">
                            <select
                                disabled
                                class="fi-select-input block w-full border-none py-1.5 pe-8 text-base text-gray-950 transition duration-75 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] dark:text-white dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] sm:text-sm sm:leading-6 ps-3 min-h-[2.25rem] [&_optgroup]:bg-white [&_optgroup]:dark:bg-gray-900 [&_option]:bg-white [&_option]:dark:bg-gray-900"
                            >
                                <option x-show="selectedLabels.length === 0 && !loading">{{ $placeholder }}</option>
                                <option x-show="loading" disabled>{{ __('filament-dcat-filters::filament-dcat-filters.modal_select.loading') }}</option>
                                <template x-for="(label, index) in selectedLabels" :key="index">
                                    <option x-text="label" selected></option>
                                </template>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- Loading indicator --}}
                <div
                    x-show="loading"
                    x-cloak
                    class="absolute inset-y-0 end-0 flex items-center pe-3 pointer-events-none"
                >
                    <svg class="animate-spin h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </div>
            </div>

            {{-- Clear button --}}
            <div
                x-show="selected.length > 0 && !loading"
                x-cloak
                class="flex items-center gap-x-3 relative z-20"
            >
                <button
                    type="button"
                    @click.stop="clear()"
                    aria-label="{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.clear_selection') }}"
                    class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-md gap-1.5 text-sm fi-link-size-md fi-color-gray fi-ac-action fi-ac-link-action focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded"
                >
                    <svg class="fi-link-icon h-5 w-5 text-gray-400 group-hover/link:text-gray-500 dark:text-gray-500 dark:group-hover/link:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span class="sr-only">{{ __('filament-dcat-filters::filament-dcat-filters.modal_select.clear_selection') }}</span>
                </button>
            </div>
        </div>

        {{-- Screen reader description --}}
        <span id="filter-{{ $filterName }}-description" class="sr-only">
            <span x-text="selected.length > 0 ? '{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.items_selected') }}: ' + selected.length : '{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.no_selection') }}'"></span>
        </span>

        {{-- Error message --}}
        <div
            x-show="error"
            x-cloak
            class="mt-1 text-sm text-danger-600 dark:text-danger-400"
            x-text="error"
        ></div>

        {{-- Hidden input for storing value --}}
        <input
            type="hidden"
            name="value"
            x-ref="hiddenInput"
            value=""
        />

    {{-- Modal dialog --}}
    <x-filament::modal
        id="{{ $modalId }}"
        width="{{ $dialogWidth }}"
        :close-by-clicking-away="false"
    >
        <x-slot name="heading">
            {{ $dialogTitle }}
        </x-slot>

        @if($modelClass)
            <livewire:cooper.filament-dcat-filters.modal-select-table
                :modelClass="$modelClass"
                :titleColumn="$titleColumn"
                :keyColumn="$keyColumn"
                :multiple="$multiple"
                :displayColumns="$displayColumns"
                :searchColumns="$searchColumns"
                :selected="[]"
                :filterKey="$filterName"
                :key="$filterName"
            />
        @endif
    </x-filament::modal>
</div>
</div>
