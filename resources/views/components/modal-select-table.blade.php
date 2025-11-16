<div class="space-y-4">
    {{-- 表格容器 --}}
    <div class="overflow-hidden">
        {{ $this->table }}
    </div>

    {{-- 底部操作栏 --}}
    <div class="flex items-center justify-between gap-4 pt-4 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <span>已选择:</span>
            <span class="font-semibold text-primary-600 dark:text-primary-400">
                {{ count($selected) }}
            </span>
            <span>项</span>

            @if(count($selected) > 0)
                <button
                    wire:click="clearSelection"
                    type="button"
                    class="ml-2 text-xs text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200 underline"
                >
                    清空选择
                </button>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <x-filament::button
                color="gray"
                wire:click="cancel"
            >
                取消
            </x-filament::button>

            <x-filament::button
                wire:click="confirm"
                :disabled="count($selected) === 0"
            >
                确定
            </x-filament::button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('livewire:init', () => {
        // 处理表格行点击
        Livewire.on('table-row-clicked', (event) => {
            @this.call('selectRow', event.key);
        });
    });
</script>
@endpush
