# 模态选择过滤器（Dcat Admin 风格）

**ModalSelectFilter** 是一个受 Dcat Admin 启发的过滤器，提供包含完整表格的模态对话框进行选择。这与现有的 SelectTableFilter（使用下拉选择组件）不同。

## 概述

受 Dcat Admin 的 `SelectTable` 字段启发，此过滤器打开一个包含完整表格的模态对话框，用户可以在其中浏览、搜索和选择记录。非常适合以下场景：

- 以表格格式可视化浏览记录
- 显示多列的复杂选择
- 为大型数据集提供更好的用户体验
- 单选或多选记录

## 功能特性

- 🎯 **模态对话框** - 可自定义宽度的全屏模态框
- 📊 **表格显示** - 显示多列，支持排序和分页
- 🔍 **可搜索** - 定义哪些列可搜索
- ✅ **单选/多选** - 支持两种选择模式
- 🎨 **可自定义** - 配置显示列、对话框大小等
- 📱 **响应式** - 移动端友好设计

## 基本用法

### 简单模型选择

```php
use Cooper\FilamentDcatFilters\Filters\ModalSelectFilter;

ModalSelectFilter::make('user_id')
    ->label('用户')
    ->model(User::class, 'name', 'id')
    ->dialogTitle('选择用户')
```

### 多选模式

```php
ModalSelectFilter::make('category_ids')
    ->label('分类')
    ->model(Category::class, 'name', 'id')
    ->multiple()
    ->dialogTitle('选择分类')
    ->dialogWidth('1000px')
```

### 使用关联关系

使用 `relationship()` 时，建议同时传入关联模型类，以确保弹窗表格和标签获取功能正常：

```php
// 推荐：直接在 relationship() 中传入 model class
ModalSelectFilter::make('author_id')
    ->label('作者')
    ->relationship('author', 'name', 'id', User::class)
    ->dialogTitle('选择作者')

// 或先设置 model()，再设置 relationship()
ModalSelectFilter::make('author_id')
    ->label('作者')
    ->model(User::class, 'name', 'id')
    ->relationship('author')
    ->dialogTitle('选择作者')
```

## 高级配置

### 自定义显示列

定义在模态表格中显示哪些列：

```php
ModalSelectFilter::make('user_id')
    ->label('用户')
    ->model(User::class, 'name', 'id')
    ->displayColumns([
        'id' => 'ID',
        'name' => '姓名',
        'email' => '邮箱',
        'created_at' => '注册时间',
    ])
    ->dialogTitle('选择用户')
```

### 可搜索列

指定哪些列应该可搜索：

```php
ModalSelectFilter::make('user_id')
    ->label('用户')
    ->model(User::class, 'name', 'id')
    ->displayColumns([
        'id' => 'ID',
        'name' => '姓名',
        'email' => '邮箱',
    ])
    ->searchable(['name', 'email'])
    ->dialogTitle('选择用户')
```

### 自定义查询修改

修改用于获取记录的查询：

```php
ModalSelectFilter::make('user_id')
    ->label('活跃用户')
    ->model(User::class, 'name', 'id')
    ->modifyQueryUsing(function ($query) {
        return $query->where('is_active', true)
            ->where('role', 'member');
    })
    ->dialogTitle('选择活跃用户')
```

### 对话框自定义

自定义模态对话框外观：

```php
ModalSelectFilter::make('product_id')
    ->label('产品')
    ->model(Product::class, 'name', 'id')
    ->dialogTitle('选择产品')
    ->dialogWidth('80%')  // 可以使用 px、% 或视口单位
    ->displayColumns([
        'sku' => 'SKU',
        'name' => '产品名称',
        'price' => '价格',
        'stock' => '库存',
    ])
```

## 完整示例

以下是展示所有功能的完整示例：

```php
use Cooper\FilamentDcatFilters\Filters\ModalSelectFilter;
use App\Models\Product;

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('order_number'),
            TextColumn::make('product.name'),
            TextColumn::make('total'),
        ])
        ->filters([
            ModalSelectFilter::make('product_id')
                ->label('产品')
                ->model(Product::class, 'name', 'id')
                ->multiple()
                ->dialogTitle('选择产品')
                ->dialogWidth('1000px')
                ->displayColumns([
                    'id' => 'ID',
                    'sku' => 'SKU',
                    'name' => '产品名称',
                    'category.name' => '分类',
                    'price' => '价格',
                    'stock' => '库存',
                ])
                ->searchable(['name', 'sku', 'category.name'])
                ->modifyQueryUsing(function ($query) {
                    return $query->with('category')
                        ->where('is_active', true)
                        ->orderBy('name');
                }),
        ]);
}
```

## Facade 用法

您也可以使用 Facade 快速访问：

```php
use Cooper\FilamentDcatFilters\Facades\FilamentDcatFilters;

FilamentDcatFilters::modalSelectFilter('user_id')
    ->label('用户')
    ->model(User::class, 'name', 'id')
    ->dialogTitle('选择用户')
```

## API 参考

### 方法

| 方法 | 描述 | 参数 |
|------|------|------|
| `model()` | 设置模型类和列 | `$modelClass, $titleColumn = 'name', $keyColumn = 'id'` |
| `relationship()` | 设置关联关系（可选传入模型类） | `$relationship, $titleColumn = 'name', $keyColumn = 'id', $modelClass = null` |
| `multiple()` | 启用多选 | `$multiple = true` |
| `dialogTitle()` | 设置模态对话框标题 | `$title` |
| `dialogWidth()` | 设置模态对话框宽度 | `$width` (例如 '900px', '80%') |
| `displayColumns()` | 设置表格中显示的列 | `array $columns` |
| `searchable()` | 设置可搜索的列 | `array $columns` |
| `modifyQueryUsing()` | 修改基础查询 | `Closure $callback` |

## 与 SelectTableFilter 的对比

| 功能 | SelectTableFilter | ModalSelectFilter |
|------|------------------|-------------------|
| UI 组件 | 下拉选择框 | 模态对话框带表格 |
| 显示方式 | 紧凑下拉 | 完整表格带多列 |
| 选择体验 | 输入搜索 | 可视化浏览 + 搜索 |
| 最适合 | 简单选择 | 需要上下文的复杂选择 |
| 灵感来源 | 标准 Filament | Dcat Admin SelectTable |

## 使用技巧

1. **明智使用显示列** - 显示相关信息但不要让表格过于拥挤
2. **启用搜索** - 对于大型数据集，使关键列可搜索
3. **设置适当宽度** - 根据列数调整对话框宽度
4. **考虑移动端** - 过滤器是响应式的，但在大屏幕上效果最佳
5. **使用关联关系** - 按相关模型过滤时，使用 `relationship()` 方法

## UI 行为

### 选中态摘要（触发器）

- **单选**：触发器按钮中显示已选标签
- **多选**：显示前 2 个标签的 badge，超过 2 项时额外显示 `+N` 溢出 badge
- 点击触发器打开弹窗；点击 badge 的 `×` 移除该项

### 自动聚焦搜索框

弹窗打开时自动聚焦搜索输入框，用户可以立即开始输入——在远程搜索和大数据集场景下尤其提速。

### 错误处理

标签加载失败（网络错误、服务器错误）时，触发器下方显示内联错误提示和**重试**按钮。重试按钮在请求进行中自动禁用，防止重复请求。已选 ID 不会丢失——仅显示标签回退为 `#id` 格式。

### 加载态稳定

触发器区域使用固定最小高度，避免在占位符、加载中、已选中状态切换时的布局跳动。

### 无障碍

- 触发器按钮：`aria-haspopup="dialog"`、动态 `aria-expanded`、`aria-label` 显示当前文本
- 移除 badge 按钮：`aria-label="移除 {label}"`
- 清除按钮：`aria-label="清除已选值"`
- 错误提示：`role="alert"` 附带重试操作

### 弹窗底部（响应式）

窄屏（< `sm` 断点）下，弹窗底部按钮竖排显示（确定/取消在上，清空在下）。宽屏下显示为单行。

### 弹窗内选中预览

选中项目后，弹窗底部除了显示数量 badge，还会预览前 1-2 个已选名称。

## 浏览器兼容性

ModalSelectFilter 使用现代 JavaScript 功能，兼容：

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- 移动浏览器（iOS Safari 14+、Chrome Mobile）

## 搜索配置

配置弹窗表格的搜索行为：

```php
ModalSelectFilter::make('user_id')
    ->model(User::class, 'name', 'id')
    ->searchable(['name', 'email'])
    ->searchDebounce(300)    // 防抖延迟毫秒（默认：300）
    ->minSearchLength(2)     // 最少输入字符数（默认：1）
```

- `searchDebounce` — 延迟搜索请求，减少快速输入时的服务端压力
- `minSearchLength` — 搜索词短于此长度时显示"请至少输入 N 个字符"的提示而非空结果，避免大表上的宽泛查询。搜索框的 placeholder 也会体现最小长度要求。

默认值可在 `config/filament-dcat-filters.php` 的 `remote_search.debounce` 和 `remote_search.min_length` 中全局配置。每个 filter 的设置会覆盖配置默认值。

## 故障排除

### 模态框无法打开
- 确保 Livewire 已正确安装和配置
- 检查浏览器控制台是否有 JavaScript 错误
- 验证模型类存在且可访问

### 记录未显示
- 使用 `modifyQueryUsing()` 检查模型查询
- 验证数据库连接
- 检查 `displayColumns()` 中的列名

### 搜索无效
- 确保 `searchable()` 中的列在数据库中存在
- 检查可搜索列的数据库索引

## 下一步

- [SelectTable 过滤器](select-table-filter.md) - 更简单的下拉版本
- [高级功能](advanced-features.md) - 更多自定义选项
- [使用示例](usage-example.md) - 实际案例
