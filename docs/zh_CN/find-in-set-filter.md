# FindInSetFilter

使用 MySQL 的 FIND_IN_SET 函数查询逗号分隔的值。

## 基本用法

```php
use Cooper\FilamentDcatFilters\Filters\FindInSetFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            FindInSetFilter::make('tags')
                ->options([
                    'php' => 'PHP',
                    'laravel' => 'Laravel',
                    'filament' => 'Filament',
                ]),
        ]);
}
```

## 选项配置

### 静态选项

```php
FindInSetFilter::make('categories')
    ->options([
        'tech' => '技术',
        'science' => '科学',
        'art' => '艺术',
    ])
```

### 闭包选项

```php
FindInSetFilter::make('tags')
    ->options(fn () => Tag::pluck('name', 'slug')->toArray())
```

## 多选模式

启用多选:

```php
FindInSetFilter::make('tags')
    ->options(['php' => 'PHP', 'laravel' => 'Laravel'])
    ->multiple()
```

## 可搜索

对于大量选项，启用搜索功能:

```php
FindInSetFilter::make('tags')
    ->options($manyOptions)
    ->searchable()
```

## 匹配逻辑

### 匹配任意 (OR 逻辑)

匹配选中值中的任意一个:

```php
FindInSetFilter::make('skills')
    ->options($skills)
    ->multiple()
    ->matchAny()
```

SQL: `FIND_IN_SET('php', skills) OR FIND_IN_SET('laravel', skills)`

### 匹配全部 (AND 逻辑)

匹配所有选中的值:

```php
FindInSetFilter::make('skills')
    ->options($skills)
    ->multiple()
    ->matchAll()
```

SQL: `FIND_IN_SET('php', skills) AND FIND_IN_SET('laravel', skills)`

## 自定义占位符

```php
FindInSetFilter::make('tags')
    ->options($tags)
    ->placeholder('按标签筛选...')
```

## 完整示例

```php
FindInSetFilter::make('categories')
    ->label('分类')
    ->options([
        'frontend' => '前端',
        'backend' => '后端',
        'devops' => '运维',
        'mobile' => '移动端',
    ])
    ->multiple()
    ->searchable()
    ->matchAny()
    ->placeholder('选择分类...')
    ->columnSpan(2),
```

## 使用场景

- 以逗号分隔存储的标签
- 没有多对多关系的遗留数据库设计
- 无需联表的简单分类
- 快速多值筛选

## 数据库兼容性

此过滤器使用 MySQL 的 `FIND_IN_SET()` 函数。如需 PostgreSQL 兼容，请考虑使用 `JsonFilter` 配合数组列。
