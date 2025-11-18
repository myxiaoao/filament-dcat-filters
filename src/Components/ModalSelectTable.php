<?php

namespace Cooper\FilamentDcatFilters\Components;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

/**
 * ModalSelectTable Livewire 组件
 *
 * 用于 ModalSelectFilter 的模态弹窗表格选择组件
 */
class ModalSelectTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public ?string $modelClass = null;

    public string $titleColumn = 'name';

    public string $keyColumn = 'id';

    public bool $multiple = false;

    public array $displayColumns = [];

    public array $searchColumns = [];

    public array $selected = [];

    public string $filterKey = '';

    public int $renderKey = 0;

    /**
     * 挂载组件
     */
    public function mount(
        string $modelClass,
        string $titleColumn = 'name',
        string $keyColumn = 'id',
        bool $multiple = false,
        array $displayColumns = [],
        array $searchColumns = [],
        array $selected = [],
        string $filterKey = ''
    ): void {
        $this->modelClass = $modelClass;
        $this->titleColumn = $titleColumn;
        $this->keyColumn = $keyColumn;
        $this->multiple = $multiple;
        $this->displayColumns = $displayColumns;
        $this->searchColumns = $searchColumns;
        $this->selected = $selected;
        $this->filterKey = $filterKey;
    }

    /**
     * 配置表格
     */
    public function table(Table $table): Table
    {
        $columns = $this->buildColumns();

        return $table
            ->query($this->getQuery())
            ->columns($columns)
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->searchable($this->searchColumns ? true : false)
            ->searchPlaceholder('搜索...')
            ->striped()
            ->extremePaginationLinks();
    }

    /**
     * 获取查询构建器
     */
    protected function getQuery(): Builder
    {
        if (! $this->modelClass) {
            throw new \RuntimeException('Model class is required.');
        }

        return $this->modelClass::query();
    }

    /**
     * 构建表格列
     */
    protected function buildColumns(): array
    {
        $columns = [];

        // 添加选择列（第一列）
        $columns[] = ViewColumn::make('_select')
            ->label('')
            ->view('filament-dcat-filters::components.select-column')
            ->state(fn ($record) => [
                'key' => $record->{$this->keyColumn},
                'selected' => in_array($record->{$this->keyColumn}, $this->selected),
                'multiple' => $this->multiple,
                'renderKey' => $this->renderKey,
            ])
            ->alignment(Alignment::Center)
            ->width('60px');

        // 如果没有指定显示列，使用默认列
        if (empty($this->displayColumns)) {
            $columns[] = TextColumn::make($this->keyColumn)
                ->label('ID')
                ->sortable();

            $columns[] = TextColumn::make($this->titleColumn)
                ->label('名称')
                ->searchable()
                ->sortable();
        } else {
            // 使用指定的显示列
            foreach ($this->displayColumns as $column => $label) {
                $col = TextColumn::make($column)
                    ->label($label)
                    ->sortable();

                // 如果列在搜索列中，则添加搜索功能
                if (in_array($column, $this->searchColumns)) {
                    $col->searchable();
                }

                $columns[] = $col;
            }
        }

        return $columns;
    }

    /**
     * 选择行
     */
    public function selectRow(string|int $key): void
    {
        if ($this->multiple) {
            if (in_array($key, $this->selected)) {
                $this->selected = array_values(array_diff($this->selected, [$key]));
            } else {
                $this->selected[] = $key;
            }
        } else {
            $this->selected = [$key];
        }
    }

    /**
     * 确认选择
     */
    public function confirm(): void
    {
        $this->dispatch('modal-select-confirmed',
            filterKey: $this->filterKey,
            selected: $this->selected,
            modelClass: $this->modelClass,
            titleColumn: $this->titleColumn,
            keyColumn: $this->keyColumn,
        );

        $this->dispatch('close-modal', id: 'modal-select-'.$this->filterKey);
    }

    /**
     * 取消选择
     */
    public function cancel(): void
    {
        $this->dispatch('close-modal', id: 'modal-select-'.$this->filterKey);
    }

    /**
     * 清空选择
     */
    public function clearSelection(): void
    {
        $this->selected = [];

        // 递增 renderKey 强制重新渲染所有 checkbox
        $this->renderKey++;
    }

    /**
     * 渲染组件
     */
    public function render(): View
    {
        return view('filament-dcat-filters::components.modal-select-table')
            ->layout('');
    }
}
