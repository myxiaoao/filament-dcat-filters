# SelectTable Filter（选择表格筛选器）

SelectTable Filter 提供了一个模态表格选择器来选择关联记录。它显示一个完整的 Filament 表格，具有搜索、排序和分页功能，非常适合从大型数据集中选择。

## 基础用法

```php
use Cooper\FilamentDcatFilters\Filters\SelectTableFilter;

SelectTableFilter::make('author_id')
    ->label('Author')
    ->model(\App\Models\User::class)
    ->tableColumns([
        Tables\Columns\TextColumn::make('name'),
        Tables\Columns\TextColumn::make('email'),
    ])
    ->searchable(['name', 'email']);
```

## 配置选项

### 设置模型类

```php
SelectTableFilter::make('author_id')
    ->model(\App\Models\User::class);
```

### 设置关联关系

使用 `relationship()` 时，建议传入关联模型类以确保下拉选项正常生成：

```php
// 推荐：在 relationship() 中直接传入 model class
SelectTableFilter::make('author')
    ->relationship('author', 'name', User::class)
    ->tableColumns([...]);

// 或先设置 model()
SelectTableFilter::make('author')
    ->model(User::class)
    ->relationship('author', 'name')
    ->tableColumns([...]);
```

### 表格列

定义在选择表格中显示的列：

```php
SelectTableFilter::make('category_id')
    ->model(Category::class)
    ->tableColumns([
        Tables\Columns\TextColumn::make('name')
            ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('slug'),
        Tables\Columns\TextColumn::make('posts_count')
            ->counts('posts'),
    ]);
```

### 可搜索列

在特定列上启用搜索：

```php
SelectTableFilter::make('user_id')
    ->model(User::class)
    ->searchable(['name', 'email', 'username']);

// 或简单启用搜索（默认搜索 'name'）
SelectTableFilter::make('user_id')
    ->model(User::class)
    ->searchable();

// 禁用搜索
SelectTableFilter::make('user_id')
    ->model(User::class)
    ->searchable(false);
```

### 多选

允许选择多条记录：

```php
SelectTableFilter::make('categories')
    ->model(Category::class)
    ->multiple()
    ->tableColumns([...]);
```

### 模态框宽度

自定义模态框大小：

```php
SelectTableFilter::make('author_id')
    ->model(User::class)
    ->modalWidth('5xl') // xs, sm, md, lg, xl, 2xl, 3xl, 4xl, 5xl, 6xl, 7xl
    ->tableColumns([...]);
```

### 修改查询

应用自定义查询修改：

```php
SelectTableFilter::make('active_users')
    ->model(User::class)
    ->modifyQueryUsing(fn($query) =>
        $query->where('is_active', true)
            ->whereNotNull('email_verified_at')
    )
    ->tableColumns([...]);
```

## 完整示例

### 带搜索的用户选择

```php
SelectTableFilter::make('author_id')
    ->label('Author')
    ->model(\App\Models\User::class)
    ->tableColumns([
        Tables\Columns\TextColumn::make('name')
            ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('email')
            ->searchable(),
        Tables\Columns\TextColumn::make('posts_count')
            ->counts('posts')
            ->label('Posts'),
        Tables\Columns\TextColumn::make('created_at')
            ->date()
            ->sortable(),
    ])
    ->searchable(['name', 'email'])
    ->modalWidth('4xl');
```

### 多选分类

```php
SelectTableFilter::make('categories')
    ->label('Categories')
    ->model(\App\Models\Category::class)
    ->multiple()
    ->tableColumns([
        Tables\Columns\TextColumn::make('name')
            ->searchable()
            ->sortable(),
        Tables\Columns\TextColumn::make('slug'),
        Tables\Columns\ColorColumn::make('color'),
        Tables\Columns\TextColumn::make('products_count')
            ->counts('products'),
    ])
    ->searchable(['name', 'slug'])
    ->modalWidth('3xl');
```

### 带颜色的标签选择

```php
SelectTableFilter::make('tags')
    ->label('Tags')
    ->model(\App\Models\Tag::class)
    ->multiple()
    ->tableColumns([
        Tables\Columns\TextColumn::make('name')
            ->badge()
            ->color(fn($record) => $record->color),
        Tables\Columns\TextColumn::make('slug'),
        Tables\Columns\TextColumn::make('usage_count')
            ->label('Used')
            ->sortable(),
    ])
    ->searchable(['name'])
    ->modalWidth('2xl');
```

### 仅活跃用户

```php
SelectTableFilter::make('assignee_id')
    ->label('Assignee')
    ->model(\App\Models\User::class)
    ->modifyQueryUsing(fn($query) =>
        $query->where('is_active', true)
            ->whereHas('roles', fn($q) =>
                $q->whereIn('name', ['admin', 'manager'])
            )
    )
    ->tableColumns([
        Tables\Columns\TextColumn::make('name')
            ->searchable(),
        Tables\Columns\TextColumn::make('department.name')
            ->label('Department'),
        Tables\Columns\TextColumn::make('role.name')
            ->label('Role'),
    ])
    ->searchable(['name', 'email']);
```

## 远程搜索（服务端）

对于大数据集（10k+ 行），从预加载切换为服务端搜索：

```php
SelectTableFilter::make('user_id')
    ->model(User::class)
    ->remoteSearch()                    // 启用服务端搜索
    ->searchColumns(['name', 'email'])  // 搜索列（默认：titleColumn）
    ->searchDebounce(300)               // 防抖延迟毫秒（默认：300）
    ->minSearchLength(2)                // 最少输入字符数（默认：1）
    ->optionsLimit(50)                  // 每次搜索最大结果数
```

启用 `remoteSearch()` 后：
- 选项**不会**预加载 — 下拉框为空，用户输入后才触发搜索
- 每次按键（防抖后）触发服务端 `LIKE` 查询
- 已选中的值通过 `getOptionLabelUsing` 解析显示标签

不启用 `remoteSearch()` 时行为不变（预加载所有选项到 `optionsLimit`）。

## 使用关联关系

通过关联关系筛选时，筛选器会自动处理 `whereHas` 查询：

```php
// 单选 — 传入 model class 以生成下拉选项
SelectTableFilter::make('author')
    ->relationship('author', 'name', User::class)
    ->tableColumns([...]);

// 生成：$query->whereHas('author', fn($q) => $q->where('id', $selectedId))

// 多选
SelectTableFilter::make('categories')
    ->model(Category::class)
    ->relationship('categories', 'name')
    ->multiple()
    ->tableColumns([...]);

// 生成：$query->whereHas('categories', fn($q) => $q->whereIn('id', $selectedIds))
```

## 配置文件

`config/filament-dcat-filters.php` 中的默认设置：

```php
'select_table' => [
    'modal_width' => '3xl',
    'per_page' => 10,
    'searchable' => true,
    'multiple' => false,
],
```

## 用户交互流程

### 单选
1. 用户点击筛选字段
2. 模态框打开显示完整表格
3. 用户可以搜索、排序和分页
4. 用户点击一行进行选择
5. 模态框自动关闭
6. 应用筛选

### 多选
1. 用户点击筛选字段
2. 模态框打开显示完整表格
3. 用户可以搜索、排序和分页
4. 用户通过复选框选择多行
5. 用户点击"选择 (X)"按钮
6. 模态框关闭
7. 应用筛选

## 高级功能

### 自定义显示

选中的值使用 `titleColumn` 显示：

```php
SelectTableFilter::make('category_id')
    ->relationship('category', 'name') // 'name' 是标题列
```

也可以在模型中自定义：

```php
class Category extends Model
{
    public function getDisplayNameAttribute(): string
    {
        return "{$this->name} ({$this->products_count} products)";
    }
}

// 然后使用：
SelectTableFilter::make('category_id')
    ->relationship('category', 'display_name');
```

## 使用技巧

1. **性能**：对于大型数据集，确保在可搜索列上建立适当的数据库索引
2. **UX**：保持合理的列数（3-5列）以获得更好的模态框显示效果
3. **搜索**：始终使关键列可搜索以获得更好的用户体验
4. **计数**：显示关联计数以帮助用户做出明智的选择
5. **宽度**：根据列数和内容使用适当的模态框宽度

## 与 Dcat Admin 的对比

### Dcat Admin
```php
$filter->equal('user_id')
    ->selectTable(UserTable::make())
    ->title('Select User')
    ->model(User::class, 'id', 'name');
```

### Filament Dcat Filters
```php
SelectTableFilter::make('user_id')
    ->label('User')
    ->model(User::class)
    ->tableColumns([
        Tables\Columns\TextColumn::make('name'),
        Tables\Columns\TextColumn::make('email'),
    ])
    ->searchable(['name', 'email']);
```

Filament 版本提供：
- 完整的 Filament Table Builder 集成
- 原生 Filament UI 组件
- 通过列定义提供更好的类型安全
- 更灵活的配置选项

## 限制

1. **Livewire**：需要 Livewire 3.0+
2. **模态框**：使用 Filament 的模态框系统
3. **性能**：对于非常大的表格（100,000+ 条记录）可能较慢
4. **自定义**：对于高度自定义的选择 UI，考虑构建自定义筛选器

## 未来增强功能

计划在未来版本中添加的功能：
- 模态框内的预设筛选器
- 顶部的快速搜索输入
- 最近选择以便更快地重新选择
- 选择模态框内的批量操作
- 自定义行操作
