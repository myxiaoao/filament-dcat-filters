# FilterGroup

使用 AND/OR 逻辑组合多个过滤器实现复杂筛选条件。

## 基本用法

```php
use Cooper\FilamentDcatFilters\Filters\FilterGroup;
use Cooper\FilamentDcatFilters\Filters\LikeFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            FilterGroup::make('content_search')
                ->orLogic()
                ->filters([
                    LikeFilter::make('title'),
                    LikeFilter::make('description'),
                ]),
        ]);
}
```

## 逻辑操作符

### OR 逻辑

匹配 **任一** 条件为真的记录:

```php
FilterGroup::make('search')
    ->orLogic()
    ->filters([
        LikeFilter::make('title'),
        LikeFilter::make('description'),
        LikeFilter::make('content'),
    ])
```

SQL: `WHERE (title LIKE '%term%' OR description LIKE '%term%' OR content LIKE '%term%')`

### AND 逻辑 (默认)

匹配 **所有** 条件都为真的记录:

```php
FilterGroup::make('strict_search')
    ->andLogic()
    ->filters([
        LikeFilter::make('title'),
        LikeFilter::make('author'),
    ])
```

SQL: `WHERE title LIKE '%term1%' AND author LIKE '%term2%'`

### 使用 logic 方法

```php
FilterGroup::make('search')
    ->logic('or')  // 或 'and'
    ->filters([...])
```

## 添加过滤器

```php
FilterGroup::make('product_search')
    ->filters([
        LikeFilter::make('name'),
        LikeFilter::make('sku'),
        LikeFilter::make('description'),
        LikeFilter::make('brand'),
    ])
```

## 完整示例

### 全局搜索框

```php
FilterGroup::make('global_search')
    ->label('搜索')
    ->orLogic()
    ->filters([
        LikeFilter::make('title')
            ->placeholder('标题'),
        LikeFilter::make('content')
            ->placeholder('内容'),
        LikeFilter::make('author_name')
            ->placeholder('作者'),
    ])
    ->columnSpan(2),
```

### 多字段精确匹配

```php
FilterGroup::make('exact_match')
    ->label('精确匹配')
    ->andLogic()
    ->filters([
        ComparisonFilter::make('category')
            ->eq(),
        ComparisonFilter::make('status')
            ->eq(),
    ]),
```

## 使用场景

- **全局搜索**: 使用 OR 逻辑跨多个字段搜索
- **复杂筛选**: 使用 AND 逻辑组合多个条件
- **高级搜索表单**: 将相关过滤器组合在一起
- **条件筛选**: 同时应用多个条件

## 可用方法

| 方法 | 描述 |
|------|------|
| `filters(array $filters)` | 设置子过滤器 |
| `logic(string $logic)` | 设置逻辑类型 ('and' 或 'or') |
| `andLogic()` | 使用 AND 逻辑 |
| `orLogic()` | 使用 OR 逻辑 |
| `getLogic()` | 获取当前逻辑类型 |
| `getChildFilters()` | 获取子过滤器数组 |

## 注意事项

- 子过滤器在单个表单区域中一起显示
- 每个子过滤器激活时生成自己的指示器
- 逻辑影响过滤器在 SQL WHERE 子句中的组合方式
- 可以嵌套 FilterGroup 实现更复杂的逻辑 (但不建议，影响用户体验)

## 过滤器兼容性

FilterGroup 可与任何继承自 `Filament\Tables\Filters\Filter` 的过滤器配合使用:

- LikeFilter
- ComparisonFilter
- BooleanFilter
- EnumFilter
- 等等...
