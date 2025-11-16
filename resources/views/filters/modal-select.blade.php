@php
    $filterKey = $filterName ?? 'modal_select';
    $modalId = 'modal-select-' . $filterKey;
    $modelClass = $modelClass ?? null;
    $titleColumn = $titleColumn ?? 'name';
    $keyColumn = $keyColumn ?? 'id';
    $multiple = $multiple ?? false;
    $dialogTitle = $dialogTitle ?? '选择';
    $dialogWidth = $dialogWidth ?? '900px';
    $searchColumns = $searchColumns ?? [];
    $displayColumns = $displayColumns ?? [];
    $placeholder = $placeholder ?? ($multiple ? 'Select items...' : 'Select item...');
    $label = $filterLabel ?? null;
@endphp

<div
    x-data="{
        selected: [],
        selectedLabels: [],
        open: false,

        init() {
            // 监听选择确认事件
            Livewire.on('modal-select-confirmed', (event) => {
                if (event.filterKey === '{{ $filterKey }}') {
                    this.updateSelection(event.selected, event.modelClass, event.titleColumn);
                }
            });
        },

        openModal(event) {
            // 阻止 disabled select 的默认行为
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            this.open = true;
            $dispatch('open-modal', { id: '{{ $modalId }}' });
        },

        updateSelection(selected, modelClass, titleColumn) {
            this.selected = selected;

            // 通过 Livewire 获取选中项的标签
            @if($modelClass)
                fetch('{{ route('filament-dcat-filters.fetch-labels') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        model: '{{ $modelClass }}',
                        ids: selected,
                        column: titleColumn
                    })
                })
                .then(response => response.json())
                .then(data => {
                    this.selectedLabels = data.labels || [];
                    this.updateHiddenInput();
                });
            @endif
        },

        updateHiddenInput() {
            const hiddenInput = this.$refs.hiddenInput;
            if (hiddenInput) {
                hiddenInput.value = this.selected.join(',');
                hiddenInput.dispatchEvent(new Event('input'));
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
            this.updateHiddenInput();
        }
    }"
>
    {{-- Label --}}
    @if($label)
        <label class="fi-fo-field-wrp-label inline-flex items-center gap-x-3">
            <span class="text-sm font-medium leading-6 text-gray-950 dark:text-white">
                {{ $label }}
            </span>
        </label>
    @endif

    {{-- 使用 Select 组件样式的显示区域 --}}
    <div class="grid gap-y-2">
        <div class="flex items-center gap-x-3 justify-between">
            <div
                class="fi-input-wrp flex min-w-0 flex-1 relative"
            >
                {{-- 透明的点击层 --}}
                <div
                    @click="openModal($event)"
                    class="absolute inset-0 z-10 cursor-pointer"
                ></div>

                <div class="min-w-0 flex-1">
                    <div class="fi-select">
                        <select
                            disabled
                            class="fi-select-input block w-full border-none bg-transparent py-1.5 pe-8 text-base text-gray-950 transition duration-75 focus:ring-0 disabled:text-gray-500 disabled:[-webkit-text-fill-color:theme(colors.gray.500)] dark:text-white dark:disabled:text-gray-400 dark:disabled:[-webkit-text-fill-color:theme(colors.gray.400)] sm:text-sm sm:leading-6 [&:not(:has(option))]:h-9 [&_optgroup]:bg-white [&_optgroup]:dark:bg-gray-900 [&_option]:bg-white [&_option]:dark:bg-gray-900"
                        >
                            <option x-show="selectedLabels.length === 0">{{ $placeholder }}</option>
                            <template x-for="(label, index) in selectedLabels" :key="index">
                                <option x-text="label" selected></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            {{-- 清空按钮 --}}
            <div
                x-show="selected.length > 0"
                x-cloak
                class="flex items-center gap-x-3 relative z-20"
            >
                <button
                    type="button"
                    @click.stop="clear()"
                    class="fi-link group/link relative inline-flex items-center justify-center outline-none fi-size-md gap-1.5 text-sm fi-link-size-md fi-color-gray fi-ac-action fi-ac-link-action"
                >
                    <svg class="fi-link-icon h-5 w-5 text-gray-400 group-hover/link:text-gray-500 dark:text-gray-500 dark:group-hover/link:text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    {{-- 隐藏输入框用于存储值 --}}
    <input
        type="hidden"
        name="value"
        x-ref="hiddenInput"
        value=""
    />
</div>

{{-- 模态弹窗 --}}
<x-filament::modal
    id="{{ $modalId }}"
    width="{{ $dialogWidth }}"
    :close-by-clicking-away="false"
>
    <x-slot name="heading">
        {{ $dialogTitle }}
    </x-slot>

    @if($modelClass)
        @livewire('cooper.filament-dcat-filters.modal-select-table', [
            'modelClass' => $modelClass,
            'titleColumn' => $titleColumn,
            'keyColumn' => $keyColumn,
            'multiple' => $multiple,
            'displayColumns' => $displayColumns,
            'searchColumns' => $searchColumns,
            'selected' => [],
            'filterKey' => $filterKey,
        ])
    @endif
</x-filament::modal>
