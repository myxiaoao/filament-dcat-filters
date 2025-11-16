# Scope Filter（范围筛选器）

Scope Filter 提供了类似标签页的快速筛选功能，灵感来源于 Dcat Admin 的 scope 特性。它非常适合用户需要在预定义查询条件之间快速切换的常见筛选场景。

## 基础用法

```php
use Cooper\FilamentDcatFilters\Filters\ScopeFilter;

ScopeFilter::make('status')
    ->scopes([
        'all' => ['label' => 'All'],
        'published' => [
            'label' => 'Published',
            'query' => fn($q) => $q->where('status', 'published'),
        ],
        'draft' => [
            'label' => 'Draft',
            'query' => fn($q) => $q->where('status', 'draft'),
        ],
    ])
```

## 配置选项

### 设置默认 Scope

```php
ScopeFilter::make('status')
    ->scopes([
        'all' => [
            'label' => 'All',
            'default' => true, // 此 scope 默认选中
        ],
        // ... 其他 scopes
    ])
```

### 显示样式

可以在单选按钮（默认）和下拉选择框之间切换：

```php
// 单选按钮（默认）
ScopeFilter::make('status')
    ->scopes([...])
    ->style('radio');

// 下拉选择框
ScopeFilter::make('status')
    ->scopes([...])
    ->style('select');
```

### 布局列数

对于单选按钮样式，可以控制列数：

```php
ScopeFilter::make('status')
    ->scopes([...])
    ->columns(3); // 3 列
```

### 徽章显示

在 scope 按钮上显示计数徽章：

```php
ScopeFilter::make('status')
    ->scopes([...])
    ->badge(true); // 启用徽章（如果已实现）
```

## Scope 配置

每个 scope 可以包含以下属性：

```php
[
    'scope_key' => [
        'label' => 'Display Label',       // 必需：显示给用户的标签
        'query' => fn($query) => $query,  // 可选：查询修改
        'default' => true,                // 可选：默认选中
        'badge' => fn($count) => $count,  // 可选：徽章计数
        'icon' => 'heroicon-o-check',     // 可选：图标（未来功能）
    ]
]
```

## 快捷方法

### 状态 Scopes

快速创建基于状态的常用 scopes：

```php
ScopeFilter::forStatus('status', [
    'draft' => 'Draft',
    'published' => 'Published',
    'archived' => 'Archived',
])
```

这会自动生成带有类似 `where('status', 'draft')` 查询的 scopes。

### 日期 Scopes

创建基于日期的常用 scopes：

```php
ScopeFilter::forDates('created_at')
```

这会生成以下 scopes：
- All Time（全部时间，默认）
- Today（今天）
- This Week（本周）
- This Month（本月）
- This Year（今年）

## 高级示例

### 复杂查询

```php
ScopeFilter::make('user_status')
    ->scopes([
        'all' => ['label' => 'All Users'],
        'active' => [
            'label' => 'Active',
            'query' => fn($q) => $q->where('is_active', true)
                ->whereNotNull('last_login_at'),
        ],
        'inactive' => [
            'label' => 'Inactive',
            'query' => fn($q) => $q->where('is_active', false)
                ->orWhereNull('last_login_at'),
        ],
        'premium' => [
            'label' => 'Premium',
            'query' => fn($q) => $q->whereHas('subscription',
                fn($q) => $q->where('plan', 'premium')
            ),
        ],
    ])
    ->columns(4)
```

### 关联查询

```php
ScopeFilter::make('has_posts')
    ->scopes([
        'all' => ['label' => 'All'],
        'with_posts' => [
            'label' => 'Has Posts',
            'query' => fn($q) => $q->has('posts'),
        ],
        'without_posts' => [
            'label' => 'No Posts',
            'query' => fn($q) => $q->doesntHave('posts'),
        ],
        'popular' => [
            'label' => 'Popular (10+ posts)',
            'query' => fn($q) => $q->has('posts', '>=', 10),
        ],
    ])
```

### 日期范围 Scopes

```php
ScopeFilter::make('date_range')
    ->scopes([
        'all' => ['label' => 'All Time'],
        'today' => [
            'label' => 'Today',
            'query' => fn($q) => $q->whereDate('created_at', today()),
        ],
        'yesterday' => [
            'label' => 'Yesterday',
            'query' => fn($q) => $q->whereDate('created_at', today()->subDay()),
        ],
        'last_7_days' => [
            'label' => 'Last 7 Days',
            'query' => fn($q) => $q->whereBetween('created_at', [
                now()->subDays(7),
                now()
            ]),
        ],
        'this_month' => [
            'label' => 'This Month',
            'query' => fn($q) => $q->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year),
        ],
    ])
```

## 使用技巧

1. **默认 Scope**：始终包含一个"All"（全部）scope 作为默认值，以便用户重置筛选
2. **标签清晰**：使用清晰、简洁的标签，用户可以立即理解
3. **查询效率**：保持 scope 查询的高效性以获得更好的性能
4. **逻辑分组**：将相关的 scopes 组合在一起
5. **常用优先**：将最常用的 scopes 放在前面

## 与 Dcat Admin 的对比

### Dcat Admin
```php
$filter->scope('published', 'Published')
    ->where('status', 'published');
```

### Filament Dcat Filters
```php
ScopeFilter::make('status')
    ->scopes([
        'published' => [
            'label' => 'Published',
            'query' => fn($q) => $q->where('status', 'published'),
        ],
    ])
```

Filament 版本将所有相关的 scopes 组合在一起，并通过闭包提供更灵活的复杂查询支持。
