<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Closure;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * ModalSelectFilter - Dcat Admin 风格的模态弹窗选择过滤器
 *
 * 参考 Dcat Admin 的 SelectTable 实现，提供模态弹窗表格选择功能。
 *
 * @example
 * ModalSelectFilter::make('user_id')
 *     ->label('用户')
 *     ->model(User::class, 'name', 'id')
 *     ->dialogTitle('选择用户')
 *     ->dialogWidth('900px')
 *     ->multiple()
 */
class ModalSelectFilter extends Filter
{
    protected ?string $modelClass = null;

    protected ?string $relationship = null;

    protected ?string $titleColumn = 'name';

    protected ?string $keyColumn = 'id';

    protected bool $multiple = false;

    protected ?string $dialogTitle = null;

    protected ?string $dialogWidth = '900px';

    protected ?Closure $modifyQueryUsing = null;

    protected ?Closure $tableCallback = null;

    protected array $searchColumns = [];

    protected array $displayColumns = [];

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
        $this->configureForm();
    }

    /**
     * 设置模型类
     *
     * @param  class-string<Model>  $modelClass  模型类名
     * @param  string  $titleColumn  显示字段名（如 'name'）
     * @param  string  $keyColumn  键字段名（默认 'id'）
     */
    public function model(string $modelClass, string $titleColumn = 'name', string $keyColumn = 'id'): static
    {
        $this->modelClass = $modelClass;
        $this->titleColumn = $titleColumn;
        $this->keyColumn = $keyColumn;
        $this->configureForm();

        return $this;
    }

    /**
     * 设置关联关系
     */
    public function relationship(string $relationship, string $titleColumn = 'name', string $keyColumn = 'id'): static
    {
        $this->relationship = $relationship;
        $this->titleColumn = $titleColumn;
        $this->keyColumn = $keyColumn;
        $this->configureForm();

        return $this;
    }

    /**
     * 设置弹窗标题
     */
    public function dialogTitle(string $title): static
    {
        $this->dialogTitle = $title;

        return $this;
    }

    /**
     * 设置弹窗宽度
     *
     * @param  string  $width  如 '900px', '80%'
     */
    public function dialogWidth(string $width): static
    {
        $this->dialogWidth = $width;

        return $this;
    }

    /**
     * 启用多选
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * 设置可搜索的字段
     */
    public function searchable(array $columns): static
    {
        $this->searchColumns = $columns;

        return $this;
    }

    /**
     * 设置表格显示的字段
     *
     * @param  array  $columns  字段数组，如 ['id' => 'ID', 'name' => '名称', 'email' => '邮箱']
     */
    public function displayColumns(array $columns): static
    {
        $this->displayColumns = $columns;

        return $this;
    }

    /**
     * 自定义表格查询
     */
    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    /**
     * 配置表单组件
     */
    protected function configureForm(): void
    {
        $filterKey = $this->getName();
        $modalId = 'modal-select-'.$filterKey;

        $this->form([
            ViewField::make('value')
                ->label($this->getLabel() ?? $this->getName())
                ->view('filament-dcat-filters::filters.modal-select')
                ->viewData([
                    'filterName' => $filterKey,
                    'modalId' => $modalId,
                    'modelClass' => $this->modelClass,
                    'titleColumn' => $this->titleColumn,
                    'keyColumn' => $this->keyColumn,
                    'multiple' => $this->multiple,
                    'dialogTitle' => $this->dialogTitle ?? ($this->getLabel() ?? '选择'),
                    'dialogWidth' => $this->dialogWidth,
                    'searchColumns' => $this->searchColumns,
                    'displayColumns' => $this->displayColumns,
                    'filterLabel' => $this->getLabel() ?? $this->getName(),
                ])
                ->columnSpanFull(),
        ]);

        $this->configureQuery();
    }

    /**
     * 配置查询逻辑
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            if (empty($value)) {
                return $query;
            }

            $column = $this->getName();

            // 处理关联关系过滤
            if ($this->relationship) {
                if ($this->multiple) {
                    $values = is_array($value) ? $value : explode(',', $value);

                    return $query->whereHas(
                        $this->relationship,
                        fn (Builder $query) => $query->whereIn($this->keyColumn, $values)
                    );
                }

                return $query->whereHas(
                    $this->relationship,
                    fn (Builder $query) => $query->where($this->keyColumn, $value)
                );
            }

            // 处理直接字段过滤
            if ($this->multiple) {
                $values = is_array($value) ? $value : explode(',', $value);

                return $query->whereIn($column, $values);
            }

            return $query->where($column, $value);
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if (empty($value)) {
                return [];
            }

            $label = $this->getLabel() ?? $this->getName();
            $model = $this->modelClass;

            if (! $model) {
                return [];
            }

            if ($this->multiple) {
                $values = is_array($value) ? $value : explode(',', $value);
                $records = $model::whereIn($this->keyColumn, $values)->get();
                $names = $records->pluck($this->titleColumn)->implode(', ');

                return [
                    Indicator::make("{$label}: {$names}")
                        ->removeField('value')
                        ->removeField('modal_select'),
                ];
            }

            $record = $model::where($this->keyColumn, $value)->first();
            $name = $record ? $record->{$this->titleColumn} : $value;

            return [
                Indicator::make("{$label}: {$name}")
                    ->removeField('value')
                    ->removeField('modal_select'),
            ];
        });
    }

    /**
     * 获取模型类
     */
    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    /**
     * 获取显示字段
     */
    public function getTitleColumn(): string
    {
        return $this->titleColumn ?? 'name';
    }

    /**
     * 获取键字段
     */
    public function getKeyColumn(): string
    {
        return $this->keyColumn ?? 'id';
    }

    /**
     * 是否多选
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * 获取弹窗标题
     */
    public function getDialogTitle(): string
    {
        return $this->dialogTitle ?? ($this->getLabel() ?? '选择');
    }

    /**
     * 获取弹窗宽度
     */
    public function getDialogWidth(): string
    {
        return $this->dialogWidth ?? '900px';
    }
}
