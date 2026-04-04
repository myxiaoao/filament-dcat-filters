# Morph Relation Filter

Filter by polymorphic relationships — both MorphTo (reverse polymorphic) and MorphToMany (polymorphic many-to-many).

## MorphTo Mode

Filter by the type of a polymorphic relation. For example, "comments belonging to posts vs videos":

```php
use Cooper\FilamentDcatFilters\Filters\MorphRelationFilter;

MorphRelationFilter::make('commentable')
    ->morphTo()
    ->types([
        'post' => Post::class,
        'video' => Video::class,
    ])
```

### Custom Labels

```php
MorphRelationFilter::make('commentable')
    ->morphTo()
    ->types([
        'post' => ['model' => Post::class, 'label' => 'Articles'],
        'video' => ['model' => Video::class, 'label' => 'Videos'],
    ])
```

## MorphToMany Mode

Filter by records in a polymorphic many-to-many relationship. For example, "articles with specific tags":

```php
MorphRelationFilter::make('tags')
    ->morphToMany()
    ->model(Tag::class)
    ->titleColumn('name')
```

### Multiple Selection

```php
MorphRelationFilter::make('tags')
    ->morphToMany()
    ->model(Tag::class)
    ->titleColumn('name')
    ->multiple()
```

### With Constraints

```php
MorphRelationFilter::make('tags')
    ->morphToMany()
    ->model(Tag::class)
    ->titleColumn('name')
    ->constrainedBy(fn (Builder $q) => $q->where('is_active', true))
```

## How It Works

| Mode | Query |
|------|-------|
| MorphTo | `$query->whereHasMorph($relationship, [$modelClass])` |
| MorphToMany (single) | `$query->whereHas($relationship, fn ($q) => $q->where($key, $value))` |
| MorphToMany (multiple) | `$query->whereHas($relationship, fn ($q) => $q->whereIn($key, $values))` |

## Not Included

- **MorphOne / MorphMany**: Use [ExistsFilter](exists-filter.md) with the relationship name
- **MorphedByMany** (inverse): Too rare for v1

## API Reference

| Method | Description |
|--------|-------------|
| `morphTo()` | Set MorphTo mode |
| `morphToMany()` | Set MorphToMany mode |
| `types($array)` | Define morph types (MorphTo mode) |
| `model($class)` | Set related model (MorphToMany mode) |
| `titleColumn($col)` | Display column for options |
| `keyColumn($col)` | Key column for options |
| `constrainedBy($closure)` | Add query constraint |
| `multiple()` | Enable multiple selection (MorphToMany) |
