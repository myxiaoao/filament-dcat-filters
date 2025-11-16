# Range Filter（范围筛选器）

Range Filter 提供了简化的日期/数字范围筛选功能，自动处理单边范围。相比创建自定义筛选器，它大大减少了所需的代码量。

## 基础用法

### 日期范围

```php
use Cooper\FilamentDcatFilters\Filters\RangeFilter;

RangeFilter::make('created_at')
    ->label('Created Date')
    ->date();
```

### 日期时间范围

```php
RangeFilter::make('created_at')
    ->label('Created Time')
    ->datetime();
```

### 数值范围

```php
RangeFilter::make('price')
    ->label('Price')
    ->numeric();
```

### 整数范围

```php
RangeFilter::make('views')
    ->label('Views')
    ->integer();
```

## 配置选项

### 自定义日期格式

```php
RangeFilter::make('created_at')
    ->datetime('Y-m-d H:i')
    ->format('Y-m-d H:i');
```

### 自定义占位符

```php
// 使用命名参数
RangeFilter::make('price')
    ->numeric()
    ->placeholders('Minimum Price', 'Maximum Price');

// 使用数组
RangeFilter::make('price')
    ->numeric()
    ->placeholders(['from' => 'Min', 'to' => 'Max']);
```

### 转换为时间戳

适用于以整数形式存储的日期时间列：

```php
RangeFilter::make('created_at')
    ->datetime()
    ->toTimestamp();
```

## 筛选类型

### 日期筛选器

#### 简单日期

```php
RangeFilter::make('birth_date')
    ->label('Birth Date')
    ->date();
```

#### 带秒的日期时间

```php
RangeFilter::make('published_at')
    ->label('Published At')
    ->datetime('Y-m-d H:i:s');
```

#### 仅时间

```php
RangeFilter::make('opening_time')
    ->label('Opening Time')
    ->time();
```

### 数字筛选器

#### 小数

```php
RangeFilter::make('price')
    ->label('Price Range')
    ->numeric();
```

#### 仅整数

```php
RangeFilter::make('quantity')
    ->label('Quantity')
    ->integer();
```

## 自动范围处理

Range Filter 自动处理三种场景：

1. **提供两个值**：使用 `whereBetween($column, [$from, $to])`
2. **仅提供 'from' 值**：使用 `where($column, '>=', $from)`
3. **仅提供 'to' 值**：使用 `where($column, '<=', $to)`

示例：
```php
// 用户仅输入 "from" 值：100
// 生成的查询：WHERE views >= 100

// 用户仅输入 "to" 值：1000
// 生成的查询：WHERE views <= 1000

// 用户输入两个值：100 和 1000
// 生成的查询：WHERE views BETWEEN 100 AND 1000
```

## 高级示例

### 年龄范围

```php
RangeFilter::make('age')
    ->label('Age')
    ->integer()
    ->placeholders('Minimum Age', 'Maximum Age');
```

### 带货币的价格范围

```php
RangeFilter::make('price')
    ->label('Price (USD)')
    ->numeric()
    ->placeholders('$0', '$10,000');
```

### 自定义格式的日期范围

```php
RangeFilter::make('event_date')
    ->label('Event Date')
    ->date()
    ->format('F j, Y') // January 1, 2025
    ->placeholders('Start Date', 'End Date');
```

### 基于时间戳的筛选

```php
// 用于存储 Unix 时间戳的列
RangeFilter::make('last_login')
    ->label('Last Login')
    ->datetime()
    ->toTimestamp();
```

## 配置文件

可以在 `config/filament-dcat-filters.php` 中设置默认值：

```php
'range' => [
    'date_format' => 'Y-m-d',
    'datetime_format' => 'Y-m-d H:i:s',
    'time_format' => 'H:i:s',
    'placeholders' => [
        'from' => 'From',
        'to' => 'To',
    ],
],
```

## 筛选指示器

Range Filter 激活时会自动显示指示器：

```
Created Date from 2025-01-01
Created Date to 2025-12-31
```

或者当提供两个值时：

```
Created Date from 2025-01-01
Created Date to 2025-12-31
```

## 使用技巧

1. **单边范围**：用户可以留空一个字段以实现开放式范围
2. **日期选择器**：使用 Filament 的原生日期选择器和日历 UI
3. **验证**：输入验证由 Filament Forms 自动处理
4. **响应式**：在移动设备上运行良好
5. **清除值**：用户可以清除单个字段或整个筛选器

## 与 Dcat Admin 的对比

### Dcat Admin
```php
$filter->between('created_at')->datetime();
```

### Filament Dcat Filters
```php
RangeFilter::make('created_at')->datetime();
```

API 几乎完全相同！Filament 版本与 Filament 的表单组件无缝集成，并提供更好的开箱即用的 UI/UX。

## 常见模式

### 销售报告日期范围

```php
RangeFilter::make('order_date')
    ->label('Order Date')
    ->date()
    ->placeholders('From Date', 'To Date');
```

### 产品价格筛选

```php
RangeFilter::make('price')
    ->label('Price Range')
    ->numeric()
    ->placeholders('Min Price', 'Max Price');
```

### 分析浏览量范围

```php
RangeFilter::make('views')
    ->label('View Count')
    ->integer()
    ->placeholders('Minimum', 'Maximum');
```
