# Concerns (Traits)

本包提供了多个 trait，用于在 Filter 类和 Filament ListRecords 类中复用通用逻辑。

---

## HasLabelResolver

提供统一的 label 解析机制，用于所有需要显示可读标签的 Filter。它将之前散落在各处的 3 种不同 label 解析变体统一为一个可复用的 trait。

### 方法

| 方法 | 描述 |
|------|------|
| `resolveLabel(): string` | 解析当前 filter 的显示标签。若已配置 label 则返回该值，否则根据 filter name 自动生成（如 `created_at` → `Created at`） |
| `labelResolver(): \Closure` | 返回一个解析 label 的闭包，用于需要延迟求值的场景 |

### 使用的 Filter

所有需要在 UI 中显示标签的 Filter 均使用此 trait，包括 LikeFilter、InFilter、ComparisonFilter、BetweenFilter、BooleanFilter、NullFilter、EnumFilter、FullTextFilter、RangeFilter、RelativeDateFilter 等。

### 背景

之前 label 解析逻辑在多个 Filter 中重复实现，存在细微差异。`HasLabelResolver` 将其集中为 `resolveLabel()` 和 `labelResolver()` 两个方法，使所有 Filter 的 label 解析行为保持一致。

---

## HasColumnName

允许 filter name 与数据库列名不同，适用于需要自定义查询列的场景。

### 方法

| 方法 | 描述 |
|------|------|
| `column(string $column)` | 设置自定义数据库列名 |
| `resolveColumnName(): string` | 解析实际使用的列名（优先返回自定义列名，否则返回 filter name） |

### 使用的 Filter

ComparisonFilter, LikeFilter, InFilter, RangeFilter, EnumFilter, DateComponentFilter, RelativeDateFilter, RegexFilter, HiddenFilter, SelectTableFilter, FindInSetFilter, JsonFilter

### 用法示例

```php
use Cooper\FilamentDcatFilters\Filters\LikeFilter;

// filter name 为 'search'，但实际查询 'title' 列
LikeFilter::make('search')
    ->column('title')
    ->label('搜索标题');

// 不设置 column 时，默认使用 filter name 作为列名
LikeFilter::make('title')->label('标题');
```

---

## HasOperator

提供统一的比较操作符方法，避免各 Filter 重复定义操作符逻辑。

### 方法

| 方法 | 操作符 | 描述 |
|------|--------|------|
| `gt()` | `>` | 大于 |
| `gte()` | `>=` | 大于等于 |
| `lt()` | `<` | 小于 |
| `lte()` | `<=` | 小于等于 |
| `eq()` | `=` | 等于（默认） |
| `ne()` | `!=` | 不等于 |
| `operator(string $operator)` | 自定义 | 设置任意允许的操作符 |

### 使用的 Filter

ComparisonFilter, HiddenFilter

### 约束

使用此 trait 的类需要定义 `ALLOWED_OPERATORS` 常量，用于验证操作符合法性：

```php
const ALLOWED_OPERATORS = ['=', '!=', '>', '>=', '<', '<='];
```

### 用法示例

```php
use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;
use Cooper\FilamentDcatFilters\Filters\HiddenFilter;

// 价格大于指定值
ComparisonFilter::make('price')->gt()->label('最低价格');

// 隐藏筛选器，固定条件
HiddenFilter::make('tenant_id')->eq()->default(1);

// 自定义操作符
ComparisonFilter::make('stock')->operator('<=')->label('库存不超过');
```

---

## HasFilterPresets

保存和加载过滤器组合以便快速访问。

### 设置

```php
use Cooper\FilamentDcatFilters\Concerns\HasFilterPresets;

class ListOrders extends ListRecords
{
    use HasFilterPresets;

    protected function getFilterPresets(): array
    {
        return [
            'pending_orders' => [
                'label' => '待处理订单',
                'filters' => ['status' => 'pending', 'payment' => 'unpaid'],
                'icon' => 'heroicon-o-clock',
                'color' => 'warning',
            ],
            'high_value' => [
                'label' => '高价值订单',
                'filters' => ['total' => ['from' => 1000]],
                'icon' => 'heroicon-o-currency-dollar',
                'color' => 'success',
            ],
        ];
    }
}
```

### 预设配置

| 键 | 类型 | 描述 |
|----|------|------|
| `label` | string | 预设的显示名称 |
| `filters` | array | 要应用的过滤器值 |
| `icon` | string | 可选的 Heroicon 名称 |
| `color` | string | 可选的颜色 (gray, primary, success, warning, danger) |

### 可用方法

```php
// 获取表头操作
$actions = $this->getFilterPresetActions();

// 以编程方式应用预设
$this->applyFilterPreset(['status' => 'active']);

// 检查预设是否当前激活
$isActive = $this->isFilterPresetActive('pending_orders');

// 获取当前激活的预设键
$activePreset = $this->getActiveFilterPreset();

// 重置所有过滤器
$this->resetFilterPresets();
```

---

## HasScopeBadgeCounts

在范围过滤器标签上显示记录计数。

### 设置

```php
use Cooper\FilamentDcatFilters\Concerns\HasScopeBadgeCounts;

class ListPosts extends ListRecords
{
    use HasScopeBadgeCounts;

    public function mount(): void
    {
        parent::mount();

        $this->registerScopesForBadgeCounts([
            'all' => [],
            'published' => [
                'query' => fn ($q) => $q->where('status', 'published'),
            ],
            'draft' => [
                'query' => fn ($q) => $q->where('status', 'draft'),
            ],
        ]);
    }

    protected function getBaseQueryForScopeCounting(): Builder
    {
        return Post::query();
    }
}
```

### 可用方法

```php
// 获取特定范围的计数
$count = $this->getScopeBadgeCount('published');

// 获取所有范围计数
$counts = $this->getAllScopeBadgeCounts();

// 启用/禁用徽章计数
$this->scopeBadgeCounts(false);

// 检查是否启用
$enabled = $this->areScopeBadgeCountsEnabled();

// 格式化大数字 (1000 → 1K, 1500000 → 1.5M)
$formatted = $this->formatScopeBadgeCount(1500);

// 清除缓存
$this->clearScopeBadgeCountCache();

// 刷新特定范围的计数
$this->refreshScopeBadgeCount('published');
```

---

## HasFilterExportImport

导出和导入过滤器配置以便共享或持久化。

### 设置

```php
use Cooper\FilamentDcatFilters\Concerns\HasFilterExportImport;

class ListOrders extends ListRecords
{
    use HasFilterExportImport;
}
```

### 导出过滤器

```php
// 导出为 JSON 字符串
$json = $this->exportFilters();

// 格式化导出
$prettyJson = $this->exportFilters(formatted: true);

// 导出为 base64 (URL 安全)
$base64 = $this->exportFiltersAsBase64();

// 获取数组格式
$data = $this->getFilterExportData();
```

### 导入过滤器

```php
// 从 JSON 导入
$success = $this->importFilters($jsonString);

// 从 base64 导入
$success = $this->importFiltersFromBase64($base64String);

// 合并到现有配置 (覆盖冲突)
$success = $this->mergeFilters($jsonString, overwrite: true);

// 合并但不覆盖
$success = $this->mergeFilters($jsonString, overwrite: false);
```

### URL 分享

```php
// 生成可分享的 URL
$url = $this->getFilterShareUrl();
// 结果: https://example.com/orders?filters=eyJ2ZXJzaW9uIj...

// 从 URL 加载过滤器 (在 mount() 中调用)
public function mount(): void
{
    parent::mount();
    $this->loadFiltersFromUrl();
}
```

### 加密

```php
// 为敏感过滤器数据启用加密
$this->encryptFilters(true);

$encrypted = $this->exportFilters();
// 结果: 加密字符串

// 导入时会自动检测并解密
$this->importFilters($encrypted);
```

### 清除过滤器

```php
$this->clearImportedFilters();
```

### 导出数据格式

```json
{
    "version": "1.0",
    "timestamp": "2024-01-15T10:30:00+00:00",
    "filters": {
        "status": {"value": "active"},
        "date_range": {"from": "2024-01-01", "to": "2024-01-31"}
    }
}
```

---

## HasDatabaseDriver

检测并解析当前数据库驱动（MySQL、PostgreSQL、SQLite），供 filter 内部生成驱动特定的 SQL。

解析优先级：
1. 通过 `driver()` 在 filter 实例上手动覆盖
2. 包配置 `filament-dcat-filters.database.driver`
3. 从查询连接自动检测

### 方法

| 方法 | 描述 |
|------|------|
| `driver(string $driver): static` | 为当前 filter 实例手动指定驱动 |
| `resolveDriver(Builder $query): string` | 解析最终生效的驱动名称 |
| `isPostgres(Builder $query): bool` | 当解析到的驱动为 `pgsql` 时返回 `true` |

### 使用的 Filter

FullTextFilter、FindInSetFilter、JsonFilter、RegexFilter

---

## HasInlineLabel

提供 Dcat Admin 风格的内联标签显示——将标签渲染为输入框内部的前缀，而非显示在输入框上方。可通过全局配置或在 filter 上调用 `inlineLabel()` 单独控制。

### 方法

| 方法 | 描述 |
|------|------|
| `inlineLabel(bool $condition = true): static` | 为当前 filter 启用或禁用内联标签 |
| `placeholderFromLabel(bool $condition = true): static` | 将标签文字用作输入框的 placeholder |
| `shouldInlineLabel(): bool` | 解析内联标签是否生效（遵从配置默认值） |
| `shouldPlaceholderFromLabel(): bool` | 解析 placeholder 是否来自标签 |
| `applyInlineLabel(Component $component, string\|Closure $label): Component` | 将内联标签应用到单个表单组件 |
| `applyRangeInlineLabels(Component $from, Component $to, string\|Closure $label): void` | 将内联标签应用到 from/to 区间对 |

### 使用的 Filter

LikeFilter、ComparisonFilter、InFilter、EnumFilter、BooleanFilter、NullFilter、RangeFilter、RelativeDateFilter、BetweenFilter，以及其他渲染表单输入的 filter。

---

## HasRangeQuery

封装区间筛选（from/to）的查询逻辑。正确处理空值检测——将 `"0"` 视为有效非空值，并在 from > to 时自动交换两端值。

### 方法

| 方法 | 描述 |
|------|------|
| `isRangeValueEmpty(mixed $value): bool` | 仅对 `null` 或 `""` 返回 `true`，`0` 被视为有效值 |
| `applyRangeQuery(Builder $query, string $column, array $data): Builder` | 根据 from/to 的填写情况应用 `>=`、`<=` 或 `BETWEEN` 约束 |
| `generateRangeIndicators(array $data, string $label): array` | 返回当前激活的 from/to 指示器字符串数组 |

### 使用的 Filter

RangeFilter、BetweenFilter、DateComponentFilter

---

## HasRelationship

为 filter 添加 Eloquent 关联支持，通过 `whereHas` 查询关联模型的列，而非直接查询主模型。

### 用法示例

```php
// 单层关联
LikeFilter::make('tag_name')
    ->relationship('tags', 'name');

// 嵌套关联（深层路径）— 自动使用 Laravel 嵌套 whereHas
LikeFilter::make('country_name')
    ->relationship('author.company.country', 'name');
```

### 方法

| 方法 | 描述 |
|------|------|
| `relationship(string $name, ?string $titleColumn = null): static` | 配置关联名称和可选的标题列 |
| `hasRelationship(): bool` | 检查是否已配置关联 |
| `applyRelationshipConstraint(Builder $query, string $column, string $operator, mixed $value): Builder` | 通过 `whereHas` 应用单值约束 |
| `applyRelationshipWhereIn(Builder $query, string $column, array $values, bool $negate = false): Builder` | 通过 `whereHas` + `whereIn`/`whereNotIn` 应用多值约束 |

### 使用的 Filter

LikeFilter、InFilter、ComparisonFilter、EnumFilter

---

## HasSelectRadioDisplay

为 Select 下拉框和 Radio 单选按钮表单组件提供统一的构建入口。默认样式为 `select`，调用 `radio()` 可切换为内联单选按钮。

### 方法

| 方法 | 描述 |
|------|------|
| `radio(): static` | 切换为单选按钮显示 |
| `select(): static` | 切换为下拉选择框显示（默认） |
| `columns(array\|int\|null $columns = 3): static` | 设置单选按钮的列数布局 |
| `buildFormComponent(string $fieldName, Closure $labelResolver, array $options, string $placeholder): Select\|Radio` | 根据当前显示样式构建对应组件 |

### 使用的 Filter

InFilter、EnumFilter、BooleanFilter

---

## HasResetFilters

提供一键重置操作，清除所有已激活的 filter，在表格头部渲染一个操作按钮。

> 详细用法请参见 [reset-filters.md](reset-filters.md)。

---

## PersistsFiltersInLocalStorage

将表格 filter 状态持久化到浏览器的 LocalStorage，使 filter 在 session 过期或页面刷新后仍能保留。

> 详细用法请参见 [filter-persistence.md](filter-persistence.md)。

### 方法

| 方法 | 描述 |
|------|------|
| `getLocalStorageKey(): string` | 返回按组件类名作用域的 LocalStorage 键 |
| `initLocalStoragePersistence(): void` | 派发 JS 事件以初始化持久化 |
| `mountPersistsFiltersInLocalStorage(): void` | Livewire mount 钩子——派发恢复事件 |
| `restoreFiltersFromLocalStorage(array $filters): void` | Livewire 监听器——将恢复的 filter 写入组件状态 |

---

## PersistsFiltersInSession

将表格 filter 状态持久化到服务端 Laravel session，使 filter 在同一 session 内的页面刷新后仍能保留。

> 详细用法请参见 [filter-persistence.md](filter-persistence.md)。

### 方法

| 方法 | 描述 |
|------|------|
| `getFilterSessionKey(): string` | 返回按组件类名作用域的 session 键 |
| `bootPersistsFiltersInSession(): void` | Livewire boot 钩子——从 session 恢复 filter |
| `restoreFiltersFromSession(): void` | 从 session 读取已保存的 filter 并应用 |
| `saveFiltersToSession(): void` | 将当前 filter 写入 session |
| `clearFiltersFromSession(): void` | 从 session 删除已保存的 filter |
| `updatedTableFilters(): void` | Livewire 钩子——filter 变更时自动保存 |

---

## SyncsFiltersToUrl

通过 Livewire 的 query string 功能，将表格 filter 状态（filter 值、搜索词、排序列、排序方向）同步到浏览器 URL。每次变更会创建浏览器历史记录，支持通过返回键恢复之前的 filter 状态。

> 详细用法请参见 [url-sync.md](url-sync.md)。

### 方法

| 方法 | 描述 |
|------|------|
| `queryString(): array` | 返回 `history: true` 的 Livewire query string 配置 |
| `getFilterQueryString(): array` | 将当前 filter 状态以普通数组返回，用于手动构建 URL |
| `getShareableFilterUrl(): string` | 构建含当前 filter 状态查询参数的完整 URL |
| `resetUrlParameters(): void` | 清除所有已同步的 URL 参数 |

---

## SyncsFiltersToUrlWithoutHistory

与 `SyncsFiltersToUrl` 功能相同，但将 `history` 设为 `false`——通过 `replaceState` 而非 `pushState` 更新 URL，不会创建浏览器历史记录。

> 详细用法请参见 [url-sync.md](url-sync.md)。

---

## 组合使用 Traits

可以同时使用多个 trait:

```php
use Cooper\FilamentDcatFilters\Concerns\HasFilterPresets;
use Cooper\FilamentDcatFilters\Concerns\HasScopeBadgeCounts;
use Cooper\FilamentDcatFilters\Concerns\HasFilterExportImport;

class ListOrders extends ListRecords
{
    use HasFilterPresets;
    use HasScopeBadgeCounts;
    use HasFilterExportImport;

    // 实现所需方法...
}
```
