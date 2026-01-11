# Concerns (Traits)

本包提供了多个 trait，可在 Filament ListRecords 类中使用以添加额外功能。

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
