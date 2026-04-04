# 高级 JSON 过滤器

超越简单路径取值的结构化 JSON 查询。支持数组包含、路径存在和键存在操作。

## 基本用法

### 数组包含

检查 JSON 数组列是否包含特定值：

```php
use Cooper\FilamentDcatFilters\Filters\AdvancedJsonFilter;

AdvancedJsonFilter::make('tags')
    ->arrayContains()
```

### 使用预定义选项

```php
AdvancedJsonFilter::make('tags')
    ->arrayContains()
    ->options(['php' => 'PHP', 'js' => 'JavaScript', 'go' => 'Go'])
    ->multiple()
```

### 路径存在

检查 JSON 列中是否存在特定路径：

```php
AdvancedJsonFilter::make('metadata')
    ->pathExists('settings.theme')
```

### 键存在

检查 JSON 对象中是否包含某个顶级键：

```php
AdvancedJsonFilter::make('config')
    ->hasKey('notifications')
```

## 工作原理

| 模式 | MySQL | PostgreSQL | SQLite |
|------|-------|-----------|--------|
| arrayContains | `JSON_CONTAINS()` | `@> ::jsonb` | `json_each()`（降级） |
| pathExists | `JSON_CONTAINS_PATH()` | `jsonb_path_exists()` | 不支持 |
| hasKey | `JSON_CONTAINS_PATH()` | `? operator` | 不支持 |

## 数据库支持

- **MySQL、PostgreSQL**：所有模式完全支持
- **SQLite**：仅 `arrayContains` 可用（通过 `json_each` 子查询）。`pathExists` 和 `hasKey` 会抛出 `UnsupportedDatabaseDriverException`

## API 参考

| 方法 | 描述 |
|------|------|
| `arrayContains()` | 设置数组包含模式 |
| `pathExists($path)` | 设置路径存在模式 |
| `hasKey($key)` | 设置键存在模式 |
| `options($array)` | 预定义选项 |
| `multiple()` | 启用多选 |

## 参见

- [JSON 过滤器](json-filter.md) — 简单路径取值比较
