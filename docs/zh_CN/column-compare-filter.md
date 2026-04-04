# 列对比过滤器

按两个数据库列的关系过滤记录，如"售价高于成本"、"起始日期早于结束日期"。

## 基本用法

```php
use Cooper\FilamentDcatFilters\Filters\ColumnCompareFilter;

ColumnCompareFilter::make('profitable')
    ->leftColumn('price')
    ->rightColumn('cost')
    ->gt()
```

## 快捷工厂

```php
ColumnCompareFilter::comparing('price', 'cost')->gt()
```

## 显示模式

### 开关（默认）

使用固定操作符的二态开关：

```php
ColumnCompareFilter::make('profitable')
    ->leftColumn('price')
    ->rightColumn('cost')
    ->gt()
    ->toggle()
```

### 下拉框

用户选择操作符：

```php
ColumnCompareFilter::make('date_check')
    ->leftColumn('start_at')
    ->rightColumn('end_at')
    ->select()
```

## 操作符

```php
->gt()     // >
->gte()    // >=
->lt()     // <
->lte()    // <=
->eq()     // =
->ne()     // !=
```

## 工作原理

使用 Laravel 的 `whereColumn` 方法：

```php
$query->whereColumn('price', '>', 'cost')
```

在下拉框模式下，操作符会校验白名单防止 SQL 注入。

## API 参考

| 方法 | 描述 |
|------|------|
| `leftColumn($column)` | 设置左列 |
| `rightColumn($column)` | 设置右列 |
| `comparing($left, $right)` | 静态工厂 |
| `gt()` / `gte()` / `lt()` / `lte()` / `eq()` / `ne()` | 设置比较操作符 |
| `toggle()` | 使用二态开关模式（默认） |
| `select()` | 使用操作符下拉框 |
