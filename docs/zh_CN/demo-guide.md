# Filament Dcat Filters - 完整演示指南

本指南展示了 `cooper/filament-dcat-filters` 包中所有可用的筛选器类型及其用法。

## 访问演示

访问 `http://localhost:8000/admin/posts` 查看所有筛选器的完整演示。

---

## 筛选器类型总览

### 1️⃣ **ScopeFilter** - Tab 风格快捷筛选

**功能**：类似 Dcat Admin 的 Scope 功能，提供 Tab 风格的快捷筛选选项。

**演示示例**：

#### 示例 1：状态快捷筛选
```php
ScopeFilter::make('status_scope')
    ->label('Status Quick Filter')
    ->scopes([
        'all' => ['label' => 'All', 'default' => true],
        'published' => [
            'label' => 'Published',
            'query' => fn ($query) => $query->where('status', 'published'),
        ],
        'draft' => [
            'label' => 'Draft',
            'query' => fn ($query) => $query->where('status', 'draft'),
        ],
        'featured' => [
            'label' => 'Featured',
            'query' => fn ($query) => $query->where('is_featured', true),
        ],
    ])
    ->columns(4)
    ->columnSpan('full');
```

#### 示例 2：日期范围快捷筛选
```php
ScopeFilter::forDates('created_at')
    ->label('Date Range Quick Filter')
    ->columnSpan('full');
```
提供预设选项：今天、昨天、最近 7 天、最近 30 天、本月、上月。

#### 示例 3：组合条件筛选
```php
ScopeFilter::make('quality_price')
    ->label('Quality & Price Combo')
    ->scopes([
        'all' => ['label' => 'All', 'default' => true],
        'premium' => [
            'label' => 'Premium (Rating≥4, Price≥100)',
            'query' => fn ($query) => $query
                ->where('rating', '>=', 4.0)
                ->where('price', '>=', 100),
        ],
        'budget' => [
            'label' => 'Budget (Rating≥3.5, Price<50)',
            'query' => fn ($query) => $query
                ->where('rating', '>=', 3.5)
                ->where('price', '<', 50),
        ],
    ])
    ->columns(4);
```

---

### 2️⃣ **LikeFilter** - 文本模糊搜索

**功能**：对字符串字段进行 LIKE 模糊搜索。

**演示示例**：

```php
LikeFilter::make('title')
    ->label('Title (Like)')
    ->columnSpan(2);

LikeFilter::make('content')
    ->label('Content (Like)')
    ->columnSpan(2);
```

**SQL 生成**：`WHERE title LIKE '%输入值%'`

---

### 3️⃣ **InFilter** - 单选/多选筛选

**功能**：在预定义选项中进行单选或多选筛选。

**演示示例**：

#### 示例 1：多选（默认）
```php
InFilter::make('category')
    ->label('Category (In)')
    ->options([
        'Technology' => 'Technology',
        'Business' => 'Business',
        'Lifestyle' => 'Lifestyle',
        'Travel' => 'Travel',
        'Food' => 'Food',
        'Sports' => 'Sports',
    ])
    ->columnSpan(2);
```

#### 示例 2：单选
```php
InFilter::make('status')
    ->label('Status (In Single)')
    ->options([
        'draft' => 'Draft',
        'published' => 'Published',
        'archived' => 'Archived',
    ])
    ->columnSpan(2);
```

**SQL 生成**：
- 多选：`WHERE category IN ('Technology', 'Business')`
- 单选：`WHERE status = 'draft'`

---

### 4️⃣ **SelectTableFilter** - 关联模型选择

**功能**：从关联模型中选择记录（支持搜索）。

**演示示例**：

```php
SelectTableFilter::make('author_id')
    ->label('Author (SelectTable)')
    ->model(User::class)
    ->multiple()
    ->columnSpan(2);
```

**特点**：
- 支持单选和多选
- 支持搜索
- 自动显示关联模型的 name 字段

---

### 5️⃣ **ModalSelectFilter** - Dcat Admin 风格模态选择

**功能**：Dcat Admin 风格的模态弹窗表格选择器，在弹窗中显示完整表格供用户浏览和选择。

**演示示例**：

#### 示例 1：基本用法
```php
ModalSelectFilter::make('author_id')
    ->label('作者（模态选择）')
    ->model(User::class, 'name', 'id')
    ->dialogTitle('选择作者')
    ->columnSpan(2);
```

#### 示例 2：多选 + 自定义显示列
```php
ModalSelectFilter::make('category_ids')
    ->label('分类（模态多选）')
    ->model(Category::class, 'name', 'id')
    ->multiple()
    ->dialogTitle('选择分类')
    ->dialogWidth('1000px')
    ->displayColumns([
        'id' => 'ID',
        'name' => '名称',
        'description' => '描述',
    ])
    ->searchable(['name', 'description'])
    ->columnSpan(2);
```

#### 示例 3：使用关联关系
```php
ModalSelectFilter::make('user_id')
    ->label('用户（模态关联）')
    ->relationship('user', 'name', 'id')
    ->dialogTitle('选择用户')
    ->dialogWidth('900px')
    ->columnSpan(2);
```

**特点**：
- 模态对话框展示完整表格
- 支持单选和多选
- 可配置显示列和搜索列
- 支持分页和排序
- 可自定义弹窗宽度和标题
- 适合需要查看多列信息后选择的场景

**与 SelectTableFilter 的区别**：
- **SelectTableFilter**：下拉选择框，紧凑简洁
- **ModalSelectFilter**：模态弹窗表格，适合复杂选择

---

### 6️⃣ **RangeFilter** - 范围筛选

**功能**：对数值、日期、时间进行范围筛选（From - To）。

**演示示例**：

#### 示例 1：整数范围
```php
RangeFilter::make('views')
    ->label('Views Range')
    ->integer()
    ->placeholders('Min', 'Max')
    ->columnSpan(2);
```

#### 示例 2：数值范围
```php
RangeFilter::make('price')
    ->label('Price Range')
    ->numeric()
    ->placeholders('Min', 'Max')
    ->columnSpan(2);

RangeFilter::make('rating')
    ->label('Rating Range')
    ->numeric()
    ->placeholders('Min', 'Max')
    ->columnSpan(2);
```

#### 示例 3：日期时间范围
```php
RangeFilter::make('published_at')
    ->label('Published Date Range')
    ->datetime()
    ->placeholders('From', 'To')
    ->columnSpan(2);
```

**SQL 生成**：
- 只填 From：`WHERE views >= 100`
- 只填 To：`WHERE views <= 1000`
- 都填：`WHERE views BETWEEN 100 AND 1000`

**支持的类型**：
- `->integer()` - 整数
- `->numeric()` - 数值（支持小数）
- `->date()` - 日期
- `->datetime()` - 日期时间
- `->time()` - 时间

---

### 7️⃣ **ComparisonFilter** - 比较筛选

**功能**：对字段进行单一比较操作（=, >, >=, <, <=, !=）。

**演示示例**：

#### 示例 1：大于等于
```php
ComparisonFilter::make('views')
    ->label('Views Min (>=)')
    ->gte()
    ->integer()
    ->columnSpan(2);
```

#### 示例 2：小于等于
```php
ComparisonFilter::make('price')
    ->label('Price Max (<=)')
    ->lte()
    ->numeric()
    ->columnSpan(2);
```

#### 示例 3：等于
```php
ComparisonFilter::make('rating')
    ->label('Rating Exact (=)')
    ->eq()
    ->numeric()
    ->columnSpan(2);
```

**可用方法**：
- `->eq()` - 等于 (=)
- `->gt()` - 大于 (>)
- `->gte()` - 大于等于 (>=)
- `->lt()` - 小于 (<)
- `->lte()` - 小于等于 (<=)
- `->ne()` - 不等于 (!=)

---

### 8️⃣ **BetweenFilter** - 简化的 Between 筛选

**功能**：Between 筛选的简化版，自动生成 From 和 To 输入框。

**演示示例**：

```php
BetweenFilter::make('price')
    ->label('Price Between')
    ->numeric()
    ->columnSpan(2);

BetweenFilter::make('rating')
    ->label('Rating Between')
    ->numeric()
    ->columnSpan(2);
```

**与 RangeFilter 的区别**：
- BetweenFilter：更简洁的实现，自动处理
- RangeFilter：更灵活，支持更多配置选项

---

### 9️⃣ **TernaryFilter** - Filament 原生三态筛选

**功能**：Filament 原生的三态筛选器（True/False/All）。

**演示示例**：

```php
TernaryFilter::make('is_featured')
    ->label('Featured (Ternary)')
    ->placeholder('All')
    ->columnSpan(2);
```

---

## 布局配置

### 筛选器位置

```php
->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
```

**可选值**：
- `AboveContent` - 筛选器显示在表格上方（推荐）
- `Dropdown` - 筛选器显示在下拉面板中

### 列布局

```php
->filtersFormColumns(4)  // 4 列布局
```

**columnSpan 控制**：
- `->columnSpan(1)` - 占 1 列
- `->columnSpan(2)` - 占 2 列
- `->columnSpan('full')` - 占满整行

### 筛选模式

```php
->deferFilters()  // 延迟筛选，显示"搜索"和"重置"按钮
// 或
->deferFilters(false)  // 实时筛选，输入即生效
```

---

## 使用建议

### 1. **横向紧凑布局**（类似 Dcat Admin）

```php
->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
->filtersFormColumns(8)
->deferFilters()
```

每个筛选器使用 `->columnSpan(1)` 或 `->columnSpan(2)`。

### 2. **标准布局**（推荐用于复杂筛选）

```php
->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
->filtersFormColumns(4)
->deferFilters()
```

### 3. **实时筛选布局**（适合少量筛选器）

```php
->filtersLayout(\Filament\Tables\Enums\FiltersLayout::AboveContent)
->filtersFormColumns(3)
->deferFilters(false)
```

---

## 完整示例代码

查看 `app/Filament/Resources/Posts/Tables/PostsTable.php` 获取所有筛选器的完整配置。

---

## 测试数据

运行以下命令生成测试数据：

```bash
php artisan app:generate-test-data
```

这将生成 100 条包含各种场景的测试数据。

---

## 更多文档

- [README.md](README.md) - 安装和快速开始
- [docs/filters.md](docs/filters.md) - 详细的筛选器文档
- [docs/examples.md](docs/examples.md) - 更多使用示例

---

## 反馈与支持

如有问题或建议，请访问项目仓库提交 Issue。
