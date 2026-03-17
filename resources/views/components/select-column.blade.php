@php
    $state = $getState();
    $key = $state['key'] ?? null;
    $selected = $state['selected'] ?? false;
    $multiple = $state['multiple'] ?? false;
    $renderKey = $state['renderKey'] ?? 0;
@endphp

<div class="flex items-center justify-center">
    @if($multiple)
        {{-- Checkbox for multiple selection --}}
        <input
            type="checkbox"
            wire:key="checkbox-{{ $key }}-{{ $renderKey }}"
            wire:click="selectRow('{{ $key }}')"
            @checked($selected)
            aria-label="{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.select_row') }}"
            class="h-4 w-4 rounded border-gray-300 text-primary-600 focus:ring-primary-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 cursor-pointer"
        />
    @else
        {{-- Radio button for single selection --}}
        <input
            type="radio"
            wire:key="radio-{{ $key }}-{{ $renderKey }}"
            name="modal_select_radio"
            wire:click="selectRow('{{ $key }}')"
            @checked($selected)
            aria-label="{{ __('filament-dcat-filters::filament-dcat-filters.accessibility.select_option') }}"
            class="h-4 w-4 border-gray-300 text-primary-600 focus:ring-primary-600 dark:border-gray-600 dark:bg-gray-700 dark:ring-offset-gray-800 cursor-pointer"
        />
    @endif
</div>
