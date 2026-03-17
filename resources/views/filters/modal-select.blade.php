@php
    $modalId = 'modal-select-' . $filterName;
    $placeholder = $multiple
        ? __('filament-dcat-filters::filament-dcat-filters.modal_select.placeholder_multiple')
        : __('filament-dcat-filters::filament-dcat-filters.modal_select.placeholder_single');
    $statePath = $getStatePath();
    $wireModelAttribute = $applyStateBindingModifiers('wire:model');
    $inlineLabel = $inlineLabel ?? false;
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    <div
        x-data="{
            selected: [],
            selectedLabels: [],
            open: false,
            loading: false,
            _fetchController: null,
            error: null,

            init() {
                this._handleConfirmed = (event) => {
                    const detail = event.detail;
                    if (detail.filterKey === @json($filterName)) {
                        this.handleSelection(detail.selected, detail.modelClass, detail.titleColumn, detail.keyColumn);
                    }
                };
                window.addEventListener('modal-select-confirmed', this._handleConfirmed);
            },

            destroy() {
                if (this._handleConfirmed) {
                    window.removeEventListener('modal-select-confirmed', this._handleConfirmed);
                }
            },

            openModal() {
                this.open = true;
                this.error = null;
                this.$dispatch('open-modal', { id: @json($modalId) });
            },

            handleSelection(selected, modelClass, titleColumn, keyColumn) {
                this.selected = Array.isArray(selected) ? selected : [selected];
                this.error = null;
                this.fetchLabels(modelClass, titleColumn, keyColumn);
            },

            fetchLabels(modelClass, titleColumn, keyColumn) {
                @if($modelClass)
                if (this._fetchController) {
                    this._fetchController.abort();
                }
                this._fetchController = new AbortController();
                this.loading = true;
                const requestBody = {
                    model: modelClass || @json($modelClass ?? ''),
                    ids: this.selected,
                    column: titleColumn || @json($titleColumn ?? 'name'),
                    keyColumn: keyColumn || @json($keyColumn ?? 'id')
                };
                fetch(@json(route('filament-dcat-filters.fetch-labels')), {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || @json(csrf_token())
                    },
                    body: JSON.stringify(requestBody),
                    signal: this._fetchController.signal
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.labels && data.labels.length > 0) {
                        this.selectedLabels = data.labels;
                    } else {
                        this.selectedLabels = this.selected.map(id => '#' + id);
                    }
                    this.updateInput();
                })
                .catch(error => {
                    if (error.name === 'AbortError') return;
                    console.error('Fetch labels error:', error);
                    this.selectedLabels = this.selected.map(id => '#' + id);
                    this.updateInput();
                })
                .finally(() => {
                    this.loading = false;
                });
                @endif
            },

            updateInput() {
                const input = this.$refs.hiddenInput;
                if (input) {
                    input.value = this.selected.join(',');
                    input.dispatchEvent(new Event('input', { bubbles: true }));
                }
            },

            clear() {
                this.selected = [];
                this.selectedLabels = [];
                this.error = null;
                this.updateInput();
            },

            removeItem(index) {
                this.selected.splice(index, 1);
                this.selectedLabels.splice(index, 1);
                this.updateInput();
            },

            getDisplayText() {
                if (this.loading) return @json(__('filament-dcat-filters::filament-dcat-filters.modal_select.loading'));
                if (this.selectedLabels.length === 0) return '';
                return this.selectedLabels.join(', ');
            }
        }"
        class="fi-fo-select"
    >
        <x-filament::input.wrapper
            class="fi-fo-select"
            :prefix="$inlineLabel ? $label : null"
        >
            <div class="fi-select-input">
                <button
                    type="button"
                    x-on:click="openModal()"
                    class="fi-select-input-btn"
                    aria-haspopup="dialog"
                    :aria-label="getDisplayText() || @json($placeholder)"
                >
                    @if($multiple)
                        {{-- Multiple selection: show badges --}}
                        <div class="fi-select-input-value-ctn">
                            {{-- Placeholder when nothing selected --}}
                            <span
                                x-show="selectedLabels.length === 0 && !loading"
                                class="fi-select-input-placeholder"
                            >{{ $placeholder }}</span>

                            {{-- Loading state --}}
                            <span
                                x-show="loading"
                                x-cloak
                            >{{ __('filament-dcat-filters::filament-dcat-filters.modal_select.loading') }}</span>

                            {{-- Badges container --}}
                            <div
                                x-show="selectedLabels.length > 0 && !loading"
                                x-cloak
                                class="fi-select-input-value-badges-ctn"
                            >
                                <template x-for="(label, index) in selectedLabels" :key="index">
                                    <span class="fi-badge fi-color fi-color-warning fi-size-sm" style="--text: var(--color-600); --dark-text: var(--color-400);">
                                        <span class="fi-badge-label" x-text="label"></span>
                                        <button
                                            type="button"
                                            class="fi-badge-delete-btn"
                                            x-on:click.stop="removeItem(index)"
                                        >
                                            <x-filament::icon
                                                icon="heroicon-m-x-mark"
                                                class="fi-icon h-3.5 w-3.5"
                                            />
                                        </button>
                                    </span>
                                </template>
                            </div>
                        </div>
                    @else
                        {{-- Single selection: show text with clear button inline --}}
                        <span
                            x-show="selectedLabels.length === 0 && !loading"
                            class="fi-select-input-placeholder"
                        >{{ $placeholder }}</span>

                        <span
                            x-show="loading"
                            x-cloak
                        >{{ __('filament-dcat-filters::filament-dcat-filters.modal_select.loading') }}</span>

                        <span
                            x-show="selectedLabels.length > 0 && !loading"
                            x-cloak
                            class="fi-select-input-value-label truncate"
                            x-text="selectedLabels.join(', ')"
                        ></span>
                    @endif
                </button>
            </div>

            @if(!$multiple)
                {{-- Single selection: suffix with search icon or clear button --}}
                <x-slot name="suffix">
                    {{-- Search icon when nothing selected --}}
                    <x-filament::icon
                        icon="heroicon-m-magnifying-glass"
                        x-show="selected.length === 0 && !loading"
                        class="fi-icon"
                    />

                    {{-- Clear button when selected --}}
                    <button
                        type="button"
                        x-show="selected.length > 0 && !loading"
                        x-cloak
                        x-on:click.stop="clear()"
                    >
                        <x-filament::icon
                            icon="heroicon-m-x-mark"
                            class="fi-icon"
                        />
                    </button>

                    {{-- Loading indicator --}}
                    <x-filament::loading-indicator
                        x-show="loading"
                        x-cloak
                        class="fi-icon"
                    />
                </x-slot>
            @endif
        </x-filament::input.wrapper>

        {{-- Hidden input with Livewire binding --}}
        <input
            type="hidden"
            x-ref="hiddenInput"
            {{ $wireModelAttribute }}="{{ $statePath }}"
        />

        {{-- Modal --}}
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
</x-dynamic-component>
