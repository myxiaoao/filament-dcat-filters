# InputMaskFilter

使用输入掩码进行格式化输入，确保数据输入一致性。

## 基本用法

```php
use Cooper\FilamentDcatFilters\Filters\InputMaskFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            InputMaskFilter::make('phone')
                ->mask('(999) 999-9999'),
        ]);
}
```

## 掩码字符

| 字符 | 描述 |
|------|------|
| `9` | 数字 (0-9) |
| `a` | 字母 (a-z, A-Z) |
| `*` | 字母或数字 |

## 自定义掩码

```php
InputMaskFilter::make('product_code')
    ->mask('AAA-9999')
    ->placeholder('ABC-1234')
```

## 内置预设

### 电话号码

```php
InputMaskFilter::make('phone')
    ->phone()  // (999) 999-9999

// 自定义格式
InputMaskFilter::make('phone')
    ->phone('999-999-9999')
```

### 中国手机号

```php
InputMaskFilter::make('mobile')
    ->chinaPhone()  // 999 9999 9999
```

### 信用卡

```php
InputMaskFilter::make('card_number')
    ->creditCard()  // 9999 9999 9999 9999
```

### 日期

```php
InputMaskFilter::make('birth_date')
    ->date()  // 9999-99-99

// 自定义格式
InputMaskFilter::make('birth_date')
    ->date('99/99/9999')
```

### 时间

```php
InputMaskFilter::make('start_time')
    ->time()  // 99:99

// 带秒
InputMaskFilter::make('start_time')
    ->time('99:99:99')
```

### IP 地址

```php
InputMaskFilter::make('ip_address')
    ->ip()  // 999.999.999.999
```

### 邮政编码

```php
InputMaskFilter::make('postal_code')
    ->zipCode()  // 99999

// 扩展格式
InputMaskFilter::make('postal_code')
    ->zipCode('99999-9999')
```

### 货币

```php
InputMaskFilter::make('amount')
    ->currency()  // $0.00

// 自定义前缀
InputMaskFilter::make('amount')
    ->currency('¥')
```

## 比较操作符

### 模糊匹配 (默认)

搜索包含输入值的记录:

```php
InputMaskFilter::make('phone')
    ->phone()
    ->like()
```

### 精确匹配

要求完全匹配:

```php
InputMaskFilter::make('phone')
    ->phone()
    ->exact()
```

## 去除掩码字符

默认情况下，查询数据库前会去除掩码字符。

### 启用去除 (默认)

```php
InputMaskFilter::make('phone')
    ->phone()
    ->stripMask()  // (555) 123-4567 → 5551234567
```

### 禁用去除

保留掩码字符进行查询:

```php
InputMaskFilter::make('formatted_code')
    ->mask('AAA-999')
    ->stripMask(false)
```

### 自定义去除模式

```php
InputMaskFilter::make('phone')
    ->mask('(999) 999-9999')
    ->stripPattern('/[^0-9]/')  // 只保留数字
```

## 自定义占位符

```php
InputMaskFilter::make('phone')
    ->phone()
    ->placeholder('请输入电话号码')
```

## 完整示例

```php
InputMaskFilter::make('customer_phone')
    ->label('客户电话')
    ->phone()
    ->exact()
    ->stripPattern('/[^0-9]/')
    ->columnSpan(1),

InputMaskFilter::make('order_code')
    ->label('订单编号')
    ->mask('ORD-9999-AAA')
    ->placeholder('ORD-1234-ABC')
    ->stripMask(false)
    ->exact()
    ->columnSpan(1),
```

## 使用场景

- 电话号码搜索，格式一致
- 信用卡查询 (后4位)
- 带格式验证的日期筛选
- 产品编码搜索
- IP 地址筛选

## 注意事项

- 输入掩码使用 Alpine.js mask 插件格式
- 掩码字符帮助用户以正确格式输入数据
- 去除掩码功能确保数据库查询正常工作，无论存储格式如何
