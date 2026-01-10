# 级联选择筛选器

级联选择筛选器允许您创建依赖下拉菜单，其中子下拉菜单的选项取决于父下拉菜单中的选择。

## 基本用法

```php
use Cooper\FilamentDcatFilters\Filters\CascadingSelectFilter;

public function table(Table $table): Table
{
    return $table
        ->filters([
            CascadingSelectFilter::make('location')
                ->addLevel(
                    name: 'country_id',
                    label: '国家',
                    model: Country::class
                )
                ->addLevel(
                    name: 'state_id',
                    label: '省份',
                    model: State::class,
                    parentField: 'country_id',
                    foreignKey: 'country_id'
                )
                ->addLevel(
                    name: 'city_id',
                    label: '城市',
                    model: City::class,
                    parentField: 'state_id',
                    foreignKey: 'state_id'
                ),
        ]);
}
```

## 使用 Levels 方法

为了代码更简洁，您可以使用带数组的 `levels()` 方法：

```php
CascadingSelectFilter::make('location')
    ->levels([
        [
            'name' => 'country_id',
            'label' => '国家',
            'model' => Country::class,
        ],
        [
            'name' => 'state_id',
            'label' => '省份',
            'model' => State::class,
            'parentField' => 'country_id',
            'foreignKey' => 'country_id',
        ],
        [
            'name' => 'city_id',
            'label' => '城市',
            'model' => City::class,
            'parentField' => 'state_id',
            'foreignKey' => 'state_id',
        ],
    ]);
```

## 层级配置选项

每个层级接受以下配置：

| 选项 | 类型 | 描述 | 默认值 |
|------|------|------|--------|
| `name` | string | 字段/列名 | 必填 |
| `label` | string | 显示标签 | 必填 |
| `model` | string | Eloquent 模型类 | 必填 |
| `parentField` | string\|null | 父字段名 | `null` |
| `foreignKey` | string | 外键列 | `'parent_id'` |
| `titleColumn` | string | 选项标签列 | `'name'` |
| `keyColumn` | string | 选项值列 | `'id'` |

## 预设筛选器

### 地区筛选器

快速创建 国家 → 省份 → 城市 级联：

```php
CascadingSelectFilter::forLocation(
    countryModel: Country::class,
    stateModel: State::class,
    cityModel: City::class,
    countryLabel: '国家',  // 可选
    stateLabel: '省份',    // 可选
    cityLabel: '城市'      // 可选
);
```

### 分类筛选器

创建嵌套分类筛选器：

```php
CascadingSelectFilter::forCategory(
    model: Category::class,
    depth: 3,                  // 层级数
    rootLabel: '分类',         // 可选
    childLabel: '子分类',       // 可选
    parentColumn: 'parent_id'  // 可选
);
```

## 工作原理

1. **根级别**：第一级从模型加载所有可用选项
2. **子级别**：当选择父级时，子选项按外键过滤
3. **级联清除**：当父级改变时，所有子字段自动清空
4. **查询构建**：使用 `where()` 子句将选中的值应用于查询

## 自定义标题和键列

使用不同的列作为显示和值：

```php
CascadingSelectFilter::make('product')
    ->addLevel(
        name: 'brand_id',
        label: '品牌',
        model: Brand::class,
        titleColumn: 'brand_name',  // 显示此列
        keyColumn: 'id'             // 使用此作为值
    )
    ->addLevel(
        name: 'category_id',
        label: '分类',
        model: ProductCategory::class,
        parentField: 'brand_id',
        foreignKey: 'brand_id',
        titleColumn: 'category_title',
        keyColumn: 'category_id'
    );
```

## 筛选器指示器

每个选中的层级显示为可单独移除的独立指示器：

```
国家: 中国 × | 省份: 广东省 × | 城市: 深圳市 ×
```

## 数据库要求

您的数据库应具有适当的外键关系：

```php
// countries 表
Schema::create('countries', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->timestamps();
});

// states 表
Schema::create('states', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('country_id')->constrained();
    $table->timestamps();
});

// cities 表
Schema::create('cities', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('state_id')->constrained();
    $table->timestamps();
});
```

## 自引用分类

对于自引用模型（如嵌套分类）：

```php
// categories 表
Schema::create('categories', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->foreignId('parent_id')->nullable()->constrained('categories');
    $table->timestamps();
});

// 筛选器
CascadingSelectFilter::make('category')
    ->addLevel(
        name: 'category_id',
        label: '分类',
        model: Category::class,
        titleColumn: 'name'
    )
    ->addLevel(
        name: 'subcategory_id',
        label: '子分类',
        model: Category::class,
        parentField: 'category_id',
        foreignKey: 'parent_id',
        titleColumn: 'name'
    );
```

## 完整示例

```php
use Cooper\FilamentDcatFilters\Filters\CascadingSelectFilter;
use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;

public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name'),
            TextColumn::make('country.name'),
            TextColumn::make('state.name'),
            TextColumn::make('city.name'),
        ])
        ->filters([
            CascadingSelectFilter::make('location')
                ->label('地区')
                ->levels([
                    [
                        'name' => 'country_id',
                        'label' => '国家',
                        'model' => Country::class,
                    ],
                    [
                        'name' => 'state_id',
                        'label' => '省份',
                        'model' => State::class,
                        'parentField' => 'country_id',
                        'foreignKey' => 'country_id',
                    ],
                    [
                        'name' => 'city_id',
                        'label' => '城市',
                        'model' => City::class,
                        'parentField' => 'state_id',
                        'foreignKey' => 'state_id',
                    ],
                ]),
        ])
        ->headerActions([
            ResetFiltersAction::make(),
        ]);
}
```
