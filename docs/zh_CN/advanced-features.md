# 高级功能实现指南

## API 数据源支持

API 数据源支持允许筛选器从远程 API 动态加载选项数据。

### 实现方案

Filament 原生支持异步数据加载，可以通过以下方式实现：

```php
use Filament\Forms\Components\Select;

Select::make('user_id')
    ->label('User')
    ->searchable()
    ->getSearchResultsUsing(fn (string $search) =>
        User::where('name', 'like', "%{$search}%")
            ->limit(50)
            ->pluck('name', 'id')
    )
    ->getOptionLabelUsing(fn ($value): ?string =>
        User::find($value)?->name
    );
```

### InFilter with API Support

扩展 `InFilter` 支持 API 数据源：

```php
InFilter::make('category_id')
    ->label('Category')
    ->searchable()
    ->getSearchResultsUsing(function (string $search) {
        // 从 API 获取数据
        $response = Http::get('https://api.example.com/categories', [
            'search' => $search,
            'limit' => 50,
        ]);

        return collect($response->json('data'))
            ->pluck('name', 'id')
            ->toArray();
    })
    ->getOptionLabelUsing(function ($value) {
        // 获取单个选项标签
        $response = Http::get("https://api.example.com/categories/{$value}");
        return $response->json('data.name');
    });
```

### SelectTableFilter with API Support

`SelectTableFilter` 可以通过修改 `options()` 方法支持 API：

```php
SelectTableFilter::make('author_id')
    ->label('Author (API)')
    ->model(User::class)
    ->searchable()
    ->multiple()
    ->modifyQueryUsing(function ($query, string $search = '') {
        // 可以在这里添加远程 API 调用逻辑
        if ($search) {
            return $query->where('name', 'like', "%{$search}%");
        }
        return $query;
    });
```

---

## InputMask 客户端验证

InputMask 提供客户端输入格式化和验证。

### 实现方案

Filament 支持通过 `mask()` 方法添加输入掩码：

```php
use Filament\Forms\Components\TextInput;

TextInput::make('phone')
    ->label('Phone Number')
    ->mask('(999) 999-9999')
    ->placeholder('(555) 123-4567');
```

### 常用 Mask 模式

#### 1. 数值格式化

```php
// 货币
TextInput::make('price')
    ->label('Price')
    ->prefix('$')
    ->numeric()
    ->mask(fn (TextInput\Mask $mask) => $mask
        ->numeric()
        ->decimalPlaces(2)
        ->decimalSeparator('.')
        ->thousandsSeparator(',')
    );

// 百分比
TextInput::make('discount')
    ->label('Discount')
    ->suffix('%')
    ->numeric()
    ->mask(fn (TextInput\Mask $mask) => $mask
        ->numeric()
        ->minValue(0)
        ->maxValue(100)
    );
```

#### 2. 日期和时间

```php
// 日期
TextInput::make('birth_date')
    ->label('Birth Date')
    ->mask('99/99/9999')
    ->placeholder('MM/DD/YYYY');

// 时间
TextInput::make('appointment_time')
    ->label('Time')
    ->mask('99:99')
    ->placeholder('HH:MM');
```

#### 3. 手机和通讯

```php
// 美国手机号
TextInput::make('phone_us')
    ->label('Phone (US)')
    ->mask('(999) 999-9999')
    ->placeholder('(555) 123-4567');

// 国际手机号
TextInput::make('phone_intl')
    ->label('Phone (International)')
    ->mask('+99 (999) 999-9999')
    ->placeholder('+86 (138) 0013-8000');

// Email - 使用原生 HTML5 验证
TextInput::make('email')
    ->label('Email')
    ->email()
    ->placeholder('user@example.com');
```

#### 4. 网络地址

```php
// IP 地址
TextInput::make('ip_address')
    ->label('IP Address')
    ->mask('999.999.999.999')
    ->placeholder('192.168.1.1');

// MAC 地址
TextInput::make('mac_address')
    ->label('MAC Address')
    ->mask('**:**:**:**:**:**')
    ->placeholder('00:1A:2B:3C:4D:5E');

// URL - 使用原生 HTML5 验证
TextInput::make('website')
    ->label('Website')
    ->url()
    ->placeholder('https://example.com');
```

#### 5. 信用卡和金融

```php
// 信用卡号
TextInput::make('credit_card')
    ->label('Credit Card')
    ->mask('9999 9999 9999 9999')
    ->placeholder('1234 5678 9012 3456');

// CVV
TextInput::make('cvv')
    ->label('CVV')
    ->mask('999')
    ->placeholder('123');

// IBAN
TextInput::make('iban')
    ->label('IBAN')
    ->mask('AA99 9999 9999 9999 9999 9999')
    ->placeholder('GB82 WEST 1234 5698 7654 32');
```

### 扩展 ComparisonFilter 支持 InputMask

```php
// 扩展示例
ComparisonFilter::make('price')
    ->label('Price')
    ->gte()
    ->numeric()
    ->prefix('$')
    ->mask(fn (TextInput\Mask $mask) => $mask
        ->numeric()
        ->decimalPlaces(2)
        ->decimalSeparator('.')
        ->thousandsSeparator(',')
    );
```

### 自定义 Mask 模式

```php
use Filament\Forms\Components\TextInput;

TextInput::make('custom_code')
    ->label('Custom Code')
    ->mask(fn (TextInput\Mask $mask) => $mask
        ->pattern([
            '9' => '[0-9]',      // 数字
            'A' => '[A-Z]',      // 大写字母
            'a' => '[a-z]',      // 小写字母
            '*' => '[A-Za-z0-9]', // 字母或数字
        ])
        ->blocks([
            'code' => [
                'mask' => 'AAA-999-aaa',
                'lazy' => false,
            ],
        ])
    );
```

---

## FindInSet Filter 实现

`FindInSet` 用于查询逗号分隔的字符串字段。

### 创建 FindInSetFilter

```php
<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class FindInSetFilter extends Filter
{
    protected array $options = [];

    protected bool $multiple = false;

    public function options(array $options): static
    {
        $this->options = $options;
        $this->configureForm();

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        $this->configureForm();

        return $this;
    }

    protected function configureForm(): void
    {
        if (empty($this->options)) {
            return;
        }

        $label = $this->getLabel() ?? $this->getName();

        $this->form([
            Select::make($this->multiple ? 'values' : 'value')
                ->label($label)
                ->options($this->options)
                ->multiple($this->multiple)
                ->native(false)
                ->placeholder('Select...')
                ->columnSpanFull(),
        ]);

        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $column = $this->getName();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return $query;
                }

                // FIND_IN_SET for multiple values
                return $query->where(function ($query) use ($column, $values) {
                    foreach ($values as $value) {
                        $query->orWhereRaw("FIND_IN_SET(?, {$column})", [$value]);
                    }
                });
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            // FIND_IN_SET for single value
            return $query->whereRaw("FIND_IN_SET(?, {$column})", [$value]);
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->getLabel() ?? $this->getName();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return [];
                }

                $labels = array_map(fn ($value) => $this->options[$value] ?? $value, $values);

                return [
                    Indicator::make("{$label}: ".implode(', ', $labels))
                        ->removeField('values'),
                ];
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $valueLabel = $this->options[$value] ?? $value;

            return [
                Indicator::make("{$label}: {$valueLabel}")
                    ->removeField('value'),
            ];
        });
    }
}
```

### 使用示例

```php
use Cooper\FilamentDcatFilters\Filters\FindInSetFilter;

FindInSetFilter::make('tags')
    ->label('Tags')
    ->options([
        'php' => 'PHP',
        'javascript' => 'JavaScript',
        'python' => 'Python',
        'ruby' => 'Ruby',
        'java' => 'Java',
    ])
    ->multiple();
```

---

## HiddenFilter 使用说明

`HiddenFilter` 用于通过 URL 参数传递筛选条件，不会在界面上显示。

### 基本用法

```php
use Cooper\FilamentDcatFilters\Filters\HiddenFilter;

// 在 Resource 的 table() 方法中定义
HiddenFilter::make('status')
    ->default('published')
    ->eq();
```

### 通过 URL 参数使用

HiddenFilter 主要通过 URL 参数传递值：

```
# 等于筛选
/admin/posts?tableFilters[status][value]=published

# 大于等于筛选
/admin/posts?tableFilters[views][value]=1000

# 不等于筛选
/admin/posts?tableFilters[category][value]=draft
```

### 支持的运算符

```php
// 等于 (=)
HiddenFilter::make('status')
    ->default('published')
    ->eq();

// 不等于 (!=)
HiddenFilter::make('status')
    ->default('draft')
    ->ne();

// 大于 (>)
HiddenFilter::make('views')
    ->default(100)
    ->gt();

// 大于等于 (>=)
HiddenFilter::make('views')
    ->default(100)
    ->gte();

// 小于 (<)
HiddenFilter::make('price')
    ->default(1000)
    ->lt();

// 小于等于 (<=)
HiddenFilter::make('price')
    ->default(1000)
    ->lte();
```

### 使用场景

1. **预设筛选条件**：从其他页面链接到列表页时预设筛选
2. **多租户系统**：自动筛选当前租户的数据
3. **权限控制**：根据用户权限自动筛选可见数据

```php
// 示例：多租户自动筛选
HiddenFilter::make('tenant_id')
    ->default(auth()->user()->tenant_id)
    ->eq();

// 示例：只显示当前用户创建的记录
HiddenFilter::make('user_id')
    ->default(auth()->id())
    ->eq();
```

---

## 总结

### 已实现功能
1. ✅ NotLike Filter - 排除文本匹配
2. ✅ NotIn Filter - 排除选项
3. ✅ Hidden Filter - 隐藏筛选器（URL 参数）
4. ✅ DateComponentFilter - Year/Month/Day 独立筛选器

### 通过 Filament 原生支持实现
5. ✅ API 数据源 - 使用 `getSearchResultsUsing()`
6. ✅ InputMask - 使用 `mask()` 方法

### 额外功能
7. ✅ FindInSet Filter - 逗号分隔字符串查询

所有功能均已实现或提供了完整的实现方案！
