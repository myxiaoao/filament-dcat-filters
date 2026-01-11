# Quick Filters（快速筛选器）

Quick Filters 为常见的筛选操作提供简单的单行 API。这些筛选器受 Dcat Admin 快速筛选方法的启发。

## LikeFilter（模糊搜索筛选器）

使用 LIKE 查询搜索文本，自动处理通配符。

### 基础用法

```php
use Cooper\FilamentDcatFilters\Filters\LikeFilter;

LikeFilter::make('title')
    ->label('Title');
```

### 配置选项

#### 通配符位置

```php
// 两端（默认）：%value%
LikeFilter::make('title')->wildcards('both');

// 开始于：value%
LikeFilter::make('title')->startsWith();

// 结束于：%value
LikeFilter::make('title')->endsWith();

// 精确匹配：value
LikeFilter::make('title')->exact();
```

#### 大小写敏感性

```php
// 不区分大小写（默认）
LikeFilter::make('title')->insensitive();

// 区分大小写
LikeFilter::make('title')->sensitive();
```

#### 自定义操作符

```php
// 使用 ILIKE（PostgreSQL）
LikeFilter::make('title')->operator('ilike');
```

#### 否定（NOT LIKE）

排除匹配模式的记录：

```php
// NOT LIKE - 排除匹配的记录
LikeFilter::make('title')
    ->label('排除标题')
    ->notLike();

// 替代写法：使用 negate() 方法
LikeFilter::make('title')
    ->label('排除标题')
    ->negate();
```

**示例：**

```php
// 排除垃圾邮件
LikeFilter::make('email')
    ->label('排除邮箱域名')
    ->endsWith()
    ->notLike(),  // 排除以输入内容结尾的邮箱

// 排除包含特定关键词的产品
LikeFilter::make('name')
    ->label('排除产品名称')
    ->notLike()
    ->insensitive(),

// 排除以特定文本开头的标题
LikeFilter::make('title')
    ->label('排除标题前缀')
    ->startsWith()
    ->negate(),
```

### 示例

```php
// 在多列中搜索（创建多个筛选器）
LikeFilter::make('title')->label('Title'),
LikeFilter::make('description')->label('Description'),

// 邮箱搜索
LikeFilter::make('email')
    ->label('Email')
    ->insensitive(),

// 代码搜索（精确）
LikeFilter::make('sku')
    ->label('SKU')
    ->exact()
    ->sensitive(),
```

---

## InFilter（选项筛选器）

通过从列表中选择一个或多个值进行筛选。

### 基础用法

```php
use Cooper\FilamentDcatFilters\Filters\InFilter;

InFilter::make('status')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ]);
```

### 配置选项

#### 多选

```php
InFilter::make('category_id')
    ->options(Category::pluck('name', 'id'))
    ->multiple(); // 使用复选框列表
```

#### 可搜索

```php
InFilter::make('tag_id')
    ->options(Tag::pluck('name', 'id'))
    ->searchable(); // 在下拉框中添加搜索
```

#### 否定（NOT IN）

排除具有特定值的记录：

```php
// NOT IN - 排除选定的值
InFilter::make('status')
    ->label('排除状态')
    ->options([
        'draft' => 'Draft',
        'archived' => 'Archived',
    ])
    ->notIn();

// 替代写法：使用 negate() 方法
InFilter::make('status')
    ->label('排除状态')
    ->options([...])
    ->negate();

// 带搜索的多选排除
InFilter::make('category_id')
    ->label('排除分类')
    ->options(Category::pluck('name', 'id'))
    ->multiple()
    ->searchable()
    ->notIn();
```

**使用场景：**

```php
// 排除特定用户角色
InFilter::make('role')
    ->label('排除角色')
    ->options([
        'guest' => '访客',
        'banned' => '已封禁',
    ])
    ->multiple()
    ->notIn(),

// 排除特定分类的产品
InFilter::make('category_id')
    ->label('排除分类')
    ->options(Category::pluck('name', 'id'))
    ->multiple()
    ->searchable()
    ->notIn(),

// 排除特定标签
InFilter::make('tag_ids')
    ->label('排除标签')
    ->options(Tag::pluck('name', 'id'))
    ->multiple()
    ->notIn(),
```

### 示例

```php
// 单选
InFilter::make('status')
    ->label('Status')
    ->options([
        'active' => 'Active',
        'inactive' => 'Inactive',
    ]),

// 带搜索的多选
InFilter::make('categories')
    ->label('Categories')
    ->options(Category::pluck('name', 'id'))
    ->multiple()
    ->searchable(),

// 带复选框列表的标签
InFilter::make('tags')
    ->label('Tags')
    ->options(Tag::pluck('name', 'id'))
    ->multiple(),
```

---

## ComparisonFilter（比较筛选器）

使用比较操作符（>, <, >=, <=, =, !=）进行筛选。

### 基础用法

```php
use Cooper\FilamentDcatFilters\Filters\ComparisonFilter;

ComparisonFilter::make('price')
    ->gt() // 大于
    ->label('Minimum Price');
```

### 操作符

```php
// 大于 (>)
ComparisonFilter::make('views')->gt();

// 大于等于 (>=)
ComparisonFilter::make('views')->gte();

// 小于 (<)
ComparisonFilter::make('views')->lt();

// 小于等于 (<=)
ComparisonFilter::make('views')->lte();

// 等于 (=)
ComparisonFilter::make('views')->eq();

// 不等于 (!=)
ComparisonFilter::make('views')->ne();
```

### 输入类型

```php
// 数值（允许小数）
ComparisonFilter::make('price')
    ->gt()
    ->numeric();

// 仅整数
ComparisonFilter::make('quantity')
    ->gte()
    ->integer();
```

### 示例

```php
// 最低价格
ComparisonFilter::make('price')
    ->label('Minimum Price')
    ->gte()
    ->numeric(),

// 最高年龄
ComparisonFilter::make('age')
    ->label('Maximum Age')
    ->lte()
    ->integer(),

// 精确数量
ComparisonFilter::make('stock')
    ->label('Stock Equals')
    ->eq()
    ->integer(),

// 排除值
ComparisonFilter::make('status_id')
    ->label('Exclude Status')
    ->ne(),
```

---

## BetweenFilter（范围筛选器）

简化的数值范围筛选（`RangeFilter` 的整数类型别名）。

### 基础用法

```php
use Cooper\FilamentDcatFilters\Filters\BetweenFilter;

BetweenFilter::make('price')
    ->label('Price Range');
```

### 示例

```php
// 价格范围
BetweenFilter::make('price')
    ->label('Price')
    ->numeric(),

// 年龄范围
BetweenFilter::make('age')
    ->label('Age Range'),

// 数量范围
BetweenFilter::make('stock')
    ->label('Stock Level'),
```

**注意**：`BetweenFilter` 本质上是以下的快捷方式：
```php
RangeFilter::make('column')->integer()
```

---

## 常见模式

### 电商产品筛选

```php
use Cooper\FilamentDcatFilters\Filters\{LikeFilter, InFilter, ComparisonFilter, BetweenFilter};

// 按名称搜索
LikeFilter::make('name')
    ->label('Product Name'),

// 分类
InFilter::make('category_id')
    ->label('Category')
    ->options(Category::pluck('name', 'id'))
    ->multiple(),

// 价格范围
BetweenFilter::make('price')
    ->label('Price Range')
    ->numeric(),

// 最低评分
ComparisonFilter::make('rating')
    ->label('Minimum Rating')
    ->gte()
    ->numeric(),

// 库存状态
InFilter::make('stock_status')
    ->options([
        'in_stock' => 'In Stock',
        'out_of_stock' => 'Out of Stock',
        'preorder' => 'Pre-order',
    ]),
```

### 用户管理

```php
// 搜索用户
LikeFilter::make('name')
    ->label('Name or Email'),

// 角色筛选
InFilter::make('role')
    ->options([
        'admin' => 'Administrator',
        'editor' => 'Editor',
        'viewer' => 'Viewer',
    ])
    ->multiple(),

// 注册日期（改用 RangeFilter）
RangeFilter::make('created_at')
    ->label('Registration Date')
    ->date(),

// 最低文章数
ComparisonFilter::make('posts_count')
    ->label('Minimum Posts')
    ->gte()
    ->integer(),
```

### 博客文章筛选

```php
// 搜索标题/内容
LikeFilter::make('title')
    ->label('Title'),

// 状态
InFilter::make('status')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
        'scheduled' => 'Scheduled',
    ]),

// 最低浏览量
ComparisonFilter::make('views')
    ->label('Minimum Views')
    ->gte()
    ->integer(),

// 字数范围
BetweenFilter::make('word_count')
    ->label('Word Count'),
```

## 配置

`config/filament-dcat-filters.php` 中的默认设置：

```php
'quick_filters' => [
    'like_operator' => 'like', // 'like' 或 'ilike'
    'case_sensitive' => false,
    'like_wildcards' => 'both', // 'both', 'start', 'end', 'none'
],
```

## 自定义列名 (column 方法)

所有快速筛选器都支持 `column()` 方法，允许筛选器名称与数据库列名不同。这在以下场景非常有用：

- 需要多个筛选器作用于同一列
- 想要更友好的筛选器名称
- 需要在 URL 参数中使用自定义名称

### LikeFilter 使用 column()

```php
// 筛选器名为 'search'，但查询 'title' 列
LikeFilter::make('search')
    ->column('title')
    ->label('搜索标题');

// 同一列上的多个筛选器
LikeFilter::make('title_contains')
    ->column('title')
    ->label('标题包含'),

LikeFilter::make('title_excludes')
    ->column('title')
    ->notLike()
    ->label('标题排除'),
```

### InFilter 使用 column()

```php
// 筛选器名为 'category_selector'，但查询 'category_id' 列
InFilter::make('category_selector')
    ->column('category_id')
    ->options(Category::pluck('name', 'id'))
    ->multiple();

// 多选和排除使用同一列
InFilter::make('include_categories')
    ->column('category_id')
    ->options(Category::pluck('name', 'id'))
    ->multiple()
    ->label('包含分类'),

InFilter::make('exclude_categories')
    ->column('category_id')
    ->options(Category::pluck('name', 'id'))
    ->multiple()
    ->notIn()
    ->label('排除分类'),
```

### ComparisonFilter 使用 column()

```php
// 最低和最高价格筛选器作用于同一 'price' 列
ComparisonFilter::make('min_price')
    ->column('price')
    ->gte()
    ->numeric()
    ->label('最低价格'),

ComparisonFilter::make('max_price')
    ->column('price')
    ->lte()
    ->numeric()
    ->label('最高价格'),
```

### 方法签名

```php
public function column(string $column): static
```

**参数：**
- `$column` - 要筛选的数据库列名

**返回：**
- 返回筛选器实例以支持链式调用

---

## 使用技巧

1. **LikeFilter**：适合文本搜索字段
2. **InFilter**：最适合具有预定义选项的分类数据
3. **ComparisonFilter**：适合数值比较
4. **BetweenFilter**：数值范围的快捷方式
5. **组合使用**：将多个快速筛选器组合在一起实现强大的筛选功能
6. **column() 方法**：当需要多个筛选器作用于同一列时，使用此方法实现灵活的筛选器命名

## 与 Dcat Admin 的对比

### Dcat Admin
```php
$filter->like('title');
$filter->in('status')->multipleSelect([...]);
$filter->gt('views');
$filter->between('price');
```

### Filament Dcat Filters
```php
LikeFilter::make('title');
InFilter::make('status')->options([...])->multiple();
ComparisonFilter::make('views')->gt();
BetweenFilter::make('price');
```

API 非常相似，使从 Dcat Admin 迁移变得简单！
