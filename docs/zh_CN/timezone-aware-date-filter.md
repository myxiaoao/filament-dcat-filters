# 时区感知日期过滤器

在用户时区和数据库时区之间自动转换的日期范围过滤。

## 问题

当 UTC+8 的用户选择"2024-06-15"时，数据库（存储 UTC）应查询 `2024-06-14 16:00:00` 至 `2024-06-15 15:59:59`。此过滤器自动处理转换。

## 基本用法

```php
use Cooper\FilamentDcatFilters\Filters\TimezoneAwareDateFilter;

TimezoneAwareDateFilter::make('created_at')
    ->userTimezone('Asia/Shanghai')
```

## 动态用户时区

从认证用户读取时区：

```php
TimezoneAwareDateFilter::make('created_at')
    ->userTimezone(fn () => auth()->user()?->timezone ?? 'UTC')
```

## 数据库时区

默认为 `'UTC'`。如果数据库使用其他时区可以覆盖：

```php
TimezoneAwareDateFilter::make('created_at')
    ->userTimezone('Asia/Shanghai')
    ->databaseTimezone('America/New_York')
```

## 日期时间模式

包含时间选择：

```php
TimezoneAwareDateFilter::make('created_at')
    ->userTimezone('Asia/Shanghai')
    ->datetime()
```

## 工作原理

1. 用户在本地时区选择日期
2. PHP 使用 Carbon 转换为数据库时区：
   - 日期模式：`startOfDay()` / `endOfDay()` 边界
   - 日期时间模式：精确时间戳
3. `whereBetween` 查询使用转换后的值
4. 指示器显示用户时区的日期（不显示转换后的值）

## API 参考

| 方法 | 描述 |
|------|------|
| `userTimezone($tz)` | 设置用户时区（字符串或 Closure） |
| `databaseTimezone($tz)` | 设置数据库时区（默认：'UTC'） |
| `date()` | 仅日期模式（默认） |
| `datetime()` | 日期+时间模式 |
