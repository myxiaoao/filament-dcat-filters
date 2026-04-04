# 聚合过滤器

按关联记录的聚合值过滤，如"订单数大于3的用户"、"平均评分高于4.0的产品"。

## 基本用法

### 计数

```php
use Cooper\FilamentDcatFilters\Filters\AggregateFilter;

AggregateFilter::make('order_count')
    ->relationship('orders')
    ->count()
    ->gte()
```

### 求和

```php
AggregateFilter::make('total_revenue')
    ->relationship('orders')
    ->sum('amount')
    ->gte()
```

### 平均值、最大值、最小值

```php
->avg('rating')    // 平均值
->max('price')     // 最大值
->min('price')     // 最小值
```

## 快捷工厂

```php
AggregateFilter::countOf('orders')->gte()
AggregateFilter::sumOf('orders', 'amount')->gte()
```

## 带约束条件

```php
AggregateFilter::make('completed_orders')
    ->relationship('orders')
    ->count()
    ->constrainedBy(fn (Builder $q) => $q->where('status', 'completed'))
    ->gte()
```

## 操作符

```php
->gt()     // >
->gte()    // >=（默认）
->lt()     // <
->lte()    // <=
->eq()     // =
->ne()     // !=
```

## 工作原理

使用 Laravel 的 `withCount` / `withSum` / `withAvg` / `withMax` / `withMin` 方法配合 `having` 子句：

```php
// "订单数 >= 3"
$query->withCount('orders')->having('orders_count', '>=', 3)

// "订单总额 >= 1000"
$query->withSum('orders', 'amount')->having('orders_sum_amount', '>=', 1000)
```

## API 参考

| 方法 | 描述 |
|------|------|
| `relationship($name)` | 设置要聚合的关联关系 |
| `count()` | 使用 COUNT 聚合 |
| `sum($column)` | 使用 SUM 聚合 |
| `avg($column)` | 使用 AVG 聚合 |
| `max($column)` | 使用 MAX 聚合 |
| `min($column)` | 使用 MIN 聚合 |
| `constrainedBy($closure)` | 为关联添加约束条件 |
| `gt()` / `gte()` / `lt()` / `lte()` / `eq()` / `ne()` | 设置比较操作符 |
| `countOf($relationship)` | 计数的静态工厂 |
| `sumOf($relationship, $column)` | 求和的静态工厂 |
