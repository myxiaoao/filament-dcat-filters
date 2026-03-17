# RegexFilter

使用正则表达式模式匹配筛选记录。

## 基本用法

```php
use Cooper\FilamentDcatFilters\Filters\RegexFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            // 用户可以输入自己的模式
            RegexFilter::make('phone'),

            // 或使用固定模式
            RegexFilter::make('email')
                ->pattern('^[a-z]+@example\.com$'),
        ]);
}
```

## 模式模式

### 用户输入模式 (默认)

用户可以输入自己的正则表达式:

```php
RegexFilter::make('description')
    ->placeholder('输入正则表达式...')
```

### 固定模式模式

使用开关应用预定义的模式:

```php
RegexFilter::make('phone')
    ->pattern('^1[3-9][0-9]{9}$')
```

设置固定模式后，过滤器显示开关而不是文本输入。

### 模式切换

```php
// 从固定模式切换到用户输入
RegexFilter::make('phone')
    ->pattern('^test$')
    ->userPattern()  // 切换回用户输入模式
```

## 大小写敏感

### 不区分大小写

```php
RegexFilter::make('email')
    ->caseInsensitive()
```

### 区分大小写 (默认)

```php
RegexFilter::make('code')
    ->caseSensitive()
```

## 内置预设

### 中国手机号

```php
RegexFilter::make('phone')
    ->chinaMobile()
```

模式: `^1[3-9][0-9]{9}$`

### 电子邮件地址

```php
RegexFilter::make('email')
    ->email()
```

模式: `^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$`

### 网址

```php
RegexFilter::make('website')
    ->url()
```

模式: `^https?://[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}`

### IPv4 地址

```php
RegexFilter::make('ip_address')
    ->ipv4()
```

模式: `^([0-9]{1,3}\.){3}[0-9]{1,3}$`

## 自定义占位符

```php
RegexFilter::make('code')
    ->placeholder('输入模式 (例如 ^ABC-[0-9]+$)')
```

## 完整示例

```php
RegexFilter::make('product_code')
    ->label('产品编码')
    ->pattern('^[A-Z]{3}-[0-9]{4}$')
    ->caseInsensitive()
    ->columnSpan(1),

RegexFilter::make('search_pattern')
    ->label('高级搜索')
    ->placeholder('输入正则表达式...')
    ->caseInsensitive()
    ->columnSpan(2),
```

## 使用场景

- 手机号格式验证
- 邮箱域名筛选 (如只显示公司邮箱)
- 产品编码模式
- 自定义ID格式
- 数据质量筛选

## 数据库兼容性

此过滤器使用 `REGEXP` 操作符，支持:
- MySQL 5.x+
- MariaDB 5.x+
- PostgreSQL (使用 `~` 或 `~*` 不区分大小写)

注意: SQLite 对正则表达式支持有限，可能需要扩展。

## 配置

可以配置最大允许的模式长度以降低 ReDoS 攻击风险：

```php
// config/filament-dcat-filters.php
'regex' => [
    'max_pattern_length' => 500, // 默认：500 个字符
],
```

超出此长度的模式将被静默忽略。
