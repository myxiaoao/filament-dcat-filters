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
            clearNotice: false,

            init() {
                this._handleConfirmed = (event) => {
                    const detail = event.detail;
                    if (detail.filterKey === @json($filterName)) {
                        this.handleSelection(detail.selected, detail.modelClass, detail.titleColumn, detail.keyColumn);
                    }
                };
                window.addEventListener('modal-select-confirmed', this._handleConfirmed);

                // Restore initial state from Livewire-bound hidden input
                this.$nextTick(() => {
                    const input = this.$refs.hiddenInput;
                    if (input && input.value) {
                        const raw = input.value;
                        this.selected = raw.split(',').filter(v => v !== '');
                        if (this.selected.length > 0) {
                            this.fetchLabels(
                                @json($modelClass ?? ''),
                                @json($titleColumn ?? 'name'),
                                @json($keyColumn ?? 'id')
                            );
                        }
                    }
                });
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
                    if (data.labels && Array.isArray(data.labels) && data.labels.length > 0) {
                        // Replace null entries (unresolved ids) with fallback '#id'
                        this.selectedLabels = data.labels.map((label, i) => label ?? ('#' + this.selected[i]));
                    } else {
                        this.selectedLabels = this.selected.map(id => '#' + id);
                    }
                    this.updateInput();
                })
                .catch(error => {
                    if (error.name === 'AbortError') return;
                    console.error('Fetch labels error:', error);
                    this.error = error.message || 'Failed to load labels';
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
                this.clearNotice = true;
                this.updateInput();
                setTimeout(() => { this.clearNotice = false; }, 2000);
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
            },

            getSummaryText() {
                if (this.selectedLabels.length === 0) return '';
                if (this.selectedLabels.length <= 2) return this.selectedLabels.join(', ');
                return this.selectedLabels.slice(0, 2).join(', ') + ' +' + (this.selectedLabels.length - 2);
            },

            retryFetch() {
                this.error = null;
                this.fetchLabels(
                    @json($modelClass ?? ''),
                    @json($titleColumn ?? 'name'),
                    @json($keyColumn ?? 'id')
                );
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
                    :aria-expanded="open"
                    :aria-label="getDisplayText() || @json($placeholder)"
                    title="{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.open_modal', ['label' => $label ?? $filterName]) }}"
                >
                    @if($multiple)
                        {{-- Multiple selection: show summary badges --}}
                        <div class="fi-select-input-value-ctn min-h-[2rem]">
                            {{-- Placeholder when nothing selected --}}
                            <span
                                x-show="selectedLabels.length === 0 && !loading && !error"
                                class="fi-select-input-placeholder"
                            >{{ $placeholder }}</span>

                            {{-- Loading state --}}
                            <span
                                x-show="loading"
                                x-cloak
                            >{{ __('filament-dcat-filters::filament-dcat-filters.modal_select.loading') }}</span>

                            {{-- Summary badges: first 2 items + overflow count --}}
                            <div
                                x-show="selectedLabels.length > 0 && !loading"
                                x-cloak
                                class="fi-select-input-value-badges-ctn"
                            >
                                <template x-for="(label, index) in selectedLabels.slice(0, 2)" :key="index">
                                    <span class="fi-badge fi-color fi-color-warning fi-size-sm" style="--text: var(--color-600); --dark-text: var(--color-400);">
                                        <span class="fi-badge-label" x-text="label"></span>
                                        <button
                                            type="button"
                                            class="fi-badge-delete-btn"
                                            x-on:click.stop="removeItem(index)"
                                            :aria-label="'{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.remove_item', ['label' => '']) }}' + label"
                                            :title="'{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.remove_item', ['label' => '']) }}' + label"
                                        >
                                            <x-filament::icon
                                                icon="heroicon-m-x-mark"
                                                class="fi-icon h-3.5 w-3.5"
                                            />
                                        </button>
                                    </span>
                                </template>
                                {{-- Overflow count badge --}}
                                <span
                                    x-show="selectedLabels.length > 2"
                                    x-cloak
                                    class="fi-badge fi-color fi-color-gray fi-size-sm cursor-default"
                                    x-text="'+' + (selectedLabels.length - 2)"
                                    :title="selectedLabels.slice(2).join(', ')"
                                    :aria-label="selectedLabels.length + ' {{ __('filament-dcat-filters::filament-dcat-filters.accessibility.selected_summary', ['count' => '']) }}'"
                                ></span>
                            </div>
                        </div>
                    @else
                        {{-- Single selection: show text with clear button inline --}}
                        <div class="min-h-[2rem] flex items-center">
                            <span
                                x-show="selectedLabels.length === 0 && !loading && !error"
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
                                :title="selectedLabels.join(', ')"
                            ></span>
                        </div>
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
                        aria-label="{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.clear_value') }}"
                        title="{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.clear_value') }}"
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

        {{-- Error state with retry --}}
        <div
            x-show="error"
            x-cloak
            class="mt-1 flex items-center gap-1.5 text-xs text-danger-600 dark:text-danger-400"
            role="alert"
        >
            <x-filament::icon
                icon="heroicon-m-exclamation-triangle"
                class="h-3.5 w-3.5 shrink-0"
            />
            <span>{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.fetch_error') }}</span>
            <button
                type="button"
                x-on:click.stop="retryFetch()"
                class="font-medium underline hover:no-underline disabled:opacity-50 disabled:no-underline disabled:cursor-not-allowed"
                :disabled="loading"
                aria-label="{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.retry_fetch') }}"
            >
                <span x-show="!loading">{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.retry_fetch') }}</span>
                <span x-show="loading" x-cloak>{{ __('filament-dcat-filters::filament-dcat-filters.modal_select.loading') }}</span>
            </button>
        </div>

        {{-- Clear notice --}}
        <div
            x-show="clearNotice"
            x-cloak
            x-transition:enter="transition ease-out duration-200"
            x-transition:leave="transition ease-in duration-150"
            class="mt-1 text-xs text-gray-500 dark:text-gray-400"
            role="status"
        >{{ __('filament-dcat-filters::filament-dcat-filters.modal_select.cleared_notice') }}</div>

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
                @php
                    $currentValue = $getState() ?? '';
                    $initialSelected = is_array($currentValue)
                        ? $currentValue
                        : array_filter(array_map('trim', explode(',', (string) $currentValue)), fn ($v) => $v !== '');
                @endphp
                <livewire:cooper.filament-dcat-filters.modal-select-table
                    :modelClass="$modelClass"
                    :titleColumn="$titleColumn"
                    :keyColumn="$keyColumn"
                    :multiple="$multiple"
                    :displayColumns="$displayColumns"
                    :searchColumns="$searchColumns"
                    :selected="$initialSelected"
                    :filterKey="$filterName"
                    :searchDebounce="$searchDebounce"
                    :minSearchLength="$minSearchLength"
                    :autoConfirmOnSelect="$autoConfirmOnSelect ?? false"
                    :key="$filterName"
                />
            @endif
        </x-filament::modal>
    </div>
</x-dynamic-component>
