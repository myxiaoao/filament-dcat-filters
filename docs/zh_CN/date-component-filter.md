# Date Component Filter（日期组件筛选器）

Date Component Filter 允许您通过从日期时间列中提取的特定日期组件（年、月或日）来筛选记录。当您需要跨所有年份筛选特定月份，或筛选特定日期时，这非常有用。

## 功能特性

- **年份筛选** - 按特定年份筛选（例如，2024 年的所有记录）
- **月份筛选** - 按月份数字筛选（例如，所有一月的记录）
- **日期筛选** - 按月份中的日期筛选（例如，所有 15 号的记录）
- **基于 SQL 函数** - 使用数据库 SQL 函数（YEAR、MONTH、DAY）
- **自动选项** - 自动生成下拉选项
- **本地化标签** - 支持月份名称的翻译键

## 基础用法

### 按年份筛选

```php
use Cooper\FilamentDcatFilters\Filters\DateComponentFilter;

DateComponentFilter::make('created_at')
    ->label('Year')
    ->year();
```

这会生成一个包含当前年份和过去 10 年的下拉列表（例如，2024、2023、2022、...、2014）。

### 按月份筛选

```php
DateComponentFilter::make('created_at')
    ->label('Month')
    ->month();
```

这会生成一个包含全部 12 个月的下拉列表，使用本地化的月份名称。

### 按日期筛选

```php
DateComponentFilter::make('created_at')
    ->label('Day')
    ->day();
```

这会生成一个包含 01-31 日期的下拉列表。

## 实际应用示例

### 生日筛选（月份和日期）

```php
use Cooper\FilamentDcatFilters\Filters\DateComponentFilter;

public static function table(Table $table): Table
{
    return $table
        ->filters([
            DateComponentFilter::make('birth_date')
                ->label('Birth Month')
                ->month(),

            DateComponentFilter::make('birth_date')
                ->label('Birth Day')
                ->day(),
        ]);
}
```

### 财务年度筛选

```php
DateComponentFilter::make('transaction_date')
    ->label('Transaction Year')
    ->year();
```

### 季节性分析

```php
// 按季度筛选
DateComponentFilter::make('order_date')
    ->label('Order Month')
    ->month();
```

## 工作原理

### SQL 查询生成

筛选器使用数据库特定的 SQL 函数来提取日期组件：

**年份筛选：**
```sql
WHERE YEAR(created_at) = ?
```

**月份筛选：**
```sql
WHERE MONTH(created_at) = ?
```

**日期筛选：**
```sql
WHERE DAY(created_at) = ?
```

### 年份范围

默认情况下，年份筛选器显示当前年份加上过去 10 年。这在源代码中配置：

```php
$currentYear = (int) date('Y');
$years = [];
for ($i = $currentYear; $i >= $currentYear - 10; $i--) {
    $years[$i] = (string) $i;
}
```

## 配置选项

### 自定义标签

```php
DateComponentFilter::make('created_at')
    ->label('Creation Year')  // 自定义标签
    ->year();
```

### 列跨度

```php
DateComponentFilter::make('created_at')
    ->year()
    ->columnSpan(2);  // 在筛选器网格中占用 2 列
```

## 本地化

月份名称自动使用翻译键进行本地化：

**翻译文件：** `resources/lang/en/filament-dcat-filters.php`

```php
'months' => [
    '01' => 'January',
    '02' => 'February',
    '03' => 'March',
    // ... 等等
],

'date_component' => [
    'year' => 'Year',
    'month' => 'Month',
    'day' => 'Day',
    'select_year' => 'Select Year',
    'select_month' => 'Select Month',
    'select_day' => 'Select Day',
],
```

## 数据库兼容性

此筛选器使用标准 SQL 函数，适用于：

- ✅ MySQL / MariaDB
- ✅ PostgreSQL
- ✅ SQLite
- ✅ SQL Server

## 与其他筛选器的对比

### vs RangeFilter

**DateComponentFilter：**
- 按组件筛选（年、月、日）
- 使用下拉选择
- 仅精确匹配

**RangeFilter：**
- 按日期范围筛选（从-到）
- 使用日期选择器
- 范围查询（BETWEEN）

### vs ScopeFilter

**DateComponentFilter：**
- 动态下拉选项
- 单个组件提取
- 数据库驱动

**ScopeFilter：**
- 预定义的 scopes
- 每个 scope 多个条件
- 应用程序驱动

## 使用场景

### 电子商务

```php
// 按月份筛选订单进行季节性分析
DateComponentFilter::make('order_date')
    ->label('Order Month')
    ->month();
```

### 人力资源 / 员工管理

```php
// 按入职年份筛选员工
DateComponentFilter::make('hire_date')
    ->label('Hire Year')
    ->year();

// 生日提醒
DateComponentFilter::make('birth_date')
    ->label('Birth Month')
    ->month();
```

### 内容管理

```php
// 按发布年份筛选文章
DateComponentFilter::make('published_at')
    ->label('Published Year')
    ->year();
```

### 财务报告

```php
// 按财政年度筛选交易
DateComponentFilter::make('transaction_date')
    ->label('Fiscal Year')
    ->year();

// 月度费用报告
DateComponentFilter::make('expense_date')
    ->label('Expense Month')
    ->month();
```

## 高级用法

### 多个组件筛选器

您可以组合多个 DateComponentFilter 实例进行精确筛选：

```php
public static function table(Table $table): Table
{
    return $table
        ->filters([
            DateComponentFilter::make('event_date')
                ->label('Event Year')
                ->year(),

            DateComponentFilter::make('event_date')
                ->label('Event Month')
                ->month(),

            DateComponentFilter::make('event_date')
                ->label('Event Day')
                ->day(),
        ])
        ->filtersFormColumns(3);  // 在 3 列中显示筛选器
}
```

这允许用户通过选择以下内容来筛选"2024 年 1 月 15 日的所有活动"：
- 年份：2024
- 月份：01（一月）
- 日期：15

## 筛选指示器

当日期组件筛选器激活时，它会显示一个指示器显示所选值：

```
Year: 2024
```

用户可以点击指示器以移除筛选。

## 性能注意事项

### 索引

为了获得最佳性能，请考虑在日期列上添加函数索引：

**MySQL：**
```sql
ALTER TABLE posts
ADD INDEX idx_created_year ((YEAR(created_at)));

ALTER TABLE posts
ADD INDEX idx_created_month ((MONTH(created_at)));
```

**PostgreSQL：**
```sql
CREATE INDEX idx_created_year ON posts (EXTRACT(YEAR FROM created_at));
CREATE INDEX idx_created_month ON posts (EXTRACT(MONTH FROM created_at));
```

### 查询性能

筛选器使用 `whereRaw()`，这会阻止列本身的索引使用。对于拥有数百万条记录的大型表，请考虑：

1. 添加函数索引（如上所示）
2. 使用具有预先计算的年/月/日列的物化视图
3. 通过在单独的列中存储年/月/日来反规范化数据

## 问题排查

### 月份名称未翻译

**问题：** 月份下拉列表显示键如"01"、"02"而不是"January"、"February"

**解决方案：** 确保已发布翻译文件：

```bash
php artisan vendor:publish --tag=filament-dcat-filters-translations
```

### SQL 函数错误

**问题：** 数据库错误"Unknown function YEAR()"

**解决方案：** 此筛选器需要 MySQL/PostgreSQL/SQLite。对于 SQLite，请确保使用 SQLite 3.x。对于其他数据库，您可能需要调整 SQL 函数语法。

## 总结

DateComponentFilter 提供了一种简单的、基于下拉列表的方式来按日期时间列的年、月或日组件进行筛选。它非常适合：

- 季节性分析
- 生日/纪念日筛选
- 财政年度报告
- 发布日期筛选
- 任何需要按日期组件而不是日期范围进行筛选的场景
