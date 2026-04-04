# 存在性过滤器

根据关联记录是否存在进行过滤，如"有评论的文章"、"没有订单的用户"。

## 基本用法

### 有关联记录

```php
use Cooper\FilamentDcatFilters\Filters\ExistsFilter;

ExistsFilter::make('has_comments')
    ->relationship('comments')
```

### 没有关联记录

```php
ExistsFilter::make('no_orders')
    ->relationship('orders')
    ->notExists()
```

## 快捷工厂

```php
ExistsFilter::forExists('comments')       // whereHas 快捷方式
ExistsFilter::forNotExists('orders')      // whereDoesntHave 快捷方式
```

## 带约束条件

按符合特定条件的关联记录过滤：

```php
ExistsFilter::make('has_published_comments')
    ->relationship('comments')
    ->constrainedBy(fn (Builder $q) => $q->where('status', 'published'))
```

## 显示模式

### 开关（默认）

```php
ExistsFilter::make('has_comments')
    ->relationship('comments')
    ->toggle()
```

### 下拉框

三态下拉：全部 / 存在 / 不存在：

```php
ExistsFilter::make('has_comments')
    ->relationship('comments')
    ->select()
```

### 单选按钮

```php
ExistsFilter::make('has_comments')
    ->relationship('comments')
    ->radio()
```

## 自定义标签

```php
ExistsFilter::make('has_comments')
    ->relationship('comments')
    ->existsLabel('有评论')
    ->notExistsLabel('无评论')
    ->allLabel('全部')
```

## 工作原理

| 值 | 查询效果 |
|----|---------|
| （空） | 不修改 |
| `exists` | `$query->whereHas($relationship, $constraint)` |
| `not_exists` | `$query->whereDoesntHave($relationship, $constraint)` |

## API 参考

| 方法 | 描述 |
|------|------|
| `relationship($name)` | 设置要检查的关联关系 |
| `notExists()` | 反转为检查不存在 |
| `constrainedBy($closure)` | 为关联检查添加查询约束 |
| `toggle()` | 使用二态开关模式（默认） |
| `select()` | 使用下拉框 |
| `radio()` | 使用单选按钮 |
| `existsLabel($label)` | 自定义"存在"标签 |
| `notExistsLabel($label)` | 自定义"不存在"标签 |
| `allLabel($label)` | 自定义"全部"标签 |
| `forExists($relationship)` | 存在检查的静态工厂 |
| `forNotExists($relationship)` | 不存在检查的静态工厂 |
