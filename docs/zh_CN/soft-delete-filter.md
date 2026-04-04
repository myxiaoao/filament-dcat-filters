# 软删除过滤器

一行代码控制软删除记录的可见性。

## 基本用法

```php
use Cooper\FilamentDcatFilters\Filters\SoftDeleteFilter;

SoftDeleteFilter::make('trashed')
```

创建三态下拉框：全部 / 不含已删除 / 包含已删除 / 仅已删除。

## 显示模式

### 下拉框（默认）

```php
SoftDeleteFilter::make('trashed')
```

### 单选按钮

```php
SoftDeleteFilter::make('trashed')->radio()
```

### 开关

二态开关 — 关闭 = 不含已删除，开启 = 包含已删除：

```php
SoftDeleteFilter::make('trashed')->toggle()
```

## 自定义标签

```php
SoftDeleteFilter::make('trashed')
    ->withoutTrashedLabel('活跃')
    ->onlyTrashedLabel('已删除')
    ->withTrashedLabel('全部记录')
```

## 工作原理

| 值 | 查询效果 |
|----|---------|
| （空） | 不修改（Laravel 默认排除已删除） |
| `with` | `$query->withTrashed()` |
| `only` | `$query->onlyTrashed()` |

## 前提条件

模型必须使用 `SoftDeletes` trait：

```php
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use SoftDeletes;
}
```

## API 参考

| 方法 | 描述 |
|------|------|
| `toggle()` | 使用二态开关模式 |
| `radio()` | 使用单选按钮显示 |
| `select()` | 使用下拉框（默认） |
| `withoutTrashedLabel($label)` | 自定义"不含已删除"标签 |
| `onlyTrashedLabel($label)` | 自定义"仅已删除"标签 |
| `withTrashedLabel($label)` | 自定义"包含已删除"标签 |
