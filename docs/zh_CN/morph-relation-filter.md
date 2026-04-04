# 多态关联过滤器

按多态关联过滤 — 支持 MorphTo（反向多态）和 MorphToMany（多态多对多）。

## MorphTo 模式

按多态关系的类型过滤。例如"属于文章的评论 vs 属于视频的评论"：

```php
use Cooper\FilamentDcatFilters\Filters\MorphRelationFilter;

MorphRelationFilter::make('commentable')
    ->morphTo()
    ->types([
        'post' => Post::class,
        'video' => Video::class,
    ])
```

### 自定义标签

```php
MorphRelationFilter::make('commentable')
    ->morphTo()
    ->types([
        'post' => ['model' => Post::class, 'label' => '文章'],
        'video' => ['model' => Video::class, 'label' => '视频'],
    ])
```

## MorphToMany 模式

按多态多对多关系中的记录过滤。例如"带有特定标签的文章"：

```php
MorphRelationFilter::make('tags')
    ->morphToMany()
    ->model(Tag::class)
    ->titleColumn('name')
```

### 多选

```php
MorphRelationFilter::make('tags')
    ->morphToMany()
    ->model(Tag::class)
    ->titleColumn('name')
    ->multiple()
```

### 带约束条件

```php
MorphRelationFilter::make('tags')
    ->morphToMany()
    ->model(Tag::class)
    ->titleColumn('name')
    ->constrainedBy(fn (Builder $q) => $q->where('is_active', true))
```

## 工作原理

| 模式 | 查询 |
|------|------|
| MorphTo | `$query->whereHasMorph($relationship, [$modelClass])` |
| MorphToMany（单选） | `$query->whereHas($relationship, fn ($q) => $q->where($key, $value))` |
| MorphToMany（多选） | `$query->whereHas($relationship, fn ($q) => $q->whereIn($key, $values))` |

## 不包含的功能

- **MorphOne / MorphMany**：使用 [ExistsFilter](exists-filter.md) 配合关联名即可
- **MorphedByMany**（反向多态多对多）：使用场景极少，不在 v1 范围

## API 参考

| 方法 | 描述 |
|------|------|
| `morphTo()` | 设置 MorphTo 模式 |
| `morphToMany()` | 设置 MorphToMany 模式 |
| `types($array)` | 定义多态类型（MorphTo 模式） |
| `model($class)` | 设置关联模型（MorphToMany 模式） |
| `titleColumn($col)` | 选项显示列 |
| `keyColumn($col)` | 选项键列 |
| `constrainedBy($closure)` | 添加查询约束 |
| `multiple()` | 启用多选（MorphToMany） |
