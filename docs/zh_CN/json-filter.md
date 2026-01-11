# JsonFilter

查询 JSON/JSONB 列，支持路径访问和比较操作符。

## 基本用法

```php
use Cooper\FilamentDcatFilters\Filters\JsonFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            JsonFilter::make('metadata')
                ->path('settings.theme')
                ->eq(),
        ]);
}
```

## JSON 路径访问

### 点号表示法

```php
JsonFilter::make('metadata')
    ->path('settings.theme')
```

生成: `metadata->settings->theme`

### 箭头表示法

```php
JsonFilter::make('metadata')
    ->path('settings->theme->color')
```

### 嵌套路径

```php
JsonFilter::make('preferences')
    ->path('display.mode.dark')
```

## 比较操作符

### 等于 (默认)

```php
JsonFilter::make('settings')
    ->path('status')
    ->eq()
```

### 不等于

```php
JsonFilter::make('settings')
    ->path('status')
    ->neq()
```

### 大于 / 小于

```php
JsonFilter::make('metadata')
    ->path('count')
    ->gt()  // 大于

JsonFilter::make('metadata')
    ->path('count')
    ->gte() // 大于或等于

JsonFilter::make('metadata')
    ->path('count')
    ->lt()  // 小于

JsonFilter::make('metadata')
    ->path('count')
    ->lte() // 小于或等于
```

### 包含 / 不包含

```php
JsonFilter::make('metadata')
    ->path('description')
    ->like()

JsonFilter::make('metadata')
    ->path('description')
    ->notLike()
```

### 自定义操作符

```php
JsonFilter::make('metadata')
    ->path('value')
    ->operator('>=')
```

## 默认值

```php
JsonFilter::make('settings')
    ->path('theme')
    ->defaultValue('dark')
```

## 完整示例

```php
JsonFilter::make('user_preferences')
    ->label('主题偏好')
    ->path('preferences.theme')
    ->eq()
    ->defaultValue('light')
    ->columnSpan(1),
```

## 支持的操作符

| 操作符 | 方法 | 描述 |
|--------|------|------|
| `=` | `eq()` | 等于 |
| `!=` | `neq()` | 不等于 |
| `>` | `gt()` | 大于 |
| `>=` | `gte()` | 大于或等于 |
| `<` | `lt()` | 小于 |
| `<=` | `lte()` | 小于或等于 |
| `like` | `like()` | 包含 |
| `not like` | `notLike()` | 不包含 |

## 数据库兼容性

此过滤器使用 `->` JSON 访问语法，支持:
- MySQL 5.7+
- MariaDB 10.2+
- PostgreSQL 9.3+
- SQLite 3.38+
