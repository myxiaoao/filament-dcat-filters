# 未来改进指南

本文档概述了尚未实现但建议在未来开发中添加的改进和功能。

## 目录

1. [重置所有筛选器按钮](#重置所有筛选器按钮)
2. [筛选器状态持久化](#筛选器状态持久化)
3. [URL 查询参数同步](#url-查询参数同步)
4. [级联筛选器依赖](#级联筛选器依赖)
5. [无障碍访问改进](#无障碍访问改进)
6. [全面的测试覆盖](#全面的测试覆盖)

---

## 重置所有筛选器按钮

### 当前限制

目前，用户必须逐个清除每个筛选器。没有单一的"重置所有"按钮来一次性清除所有活动筛选器。

### 推荐实现

添加自定义表格操作来重置所有筛选器：

```php
use Filament\Tables\Actions\Action;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->filters([...])
        ->headerActions([
            Action::make('resetFilters')
                ->label(__('重置所有筛选器'))
                ->icon('heroicon-o-x-mark')
                ->color('gray')
                ->action(function ($livewire) {
                    $livewire->tableFilters = [];
                    $livewire->resetTable();
                })
                ->visible(fn ($livewire) => count(array_filter($livewire->tableFilters)) > 0),
        ]);
}
```

### 替代方案：使用 Filament 内置功能

Filament v4 提供了内置的筛选器重置。您可以自定义其外观：

```php
->filtersFormColumns(3)
->persistFiltersInSession()
```

---

## 筛选器状态持久化

### 当前限制

刷新页面时，筛选器状态会丢失。用户会失去他们的筛选工作。

### 推荐实现

#### 方案 1：会话持久化（内置）

Filament 提供内置的会话持久化：

```php
public function table(Table $table): Table
{
    return $table
        ->filters([...])
        ->persistFiltersInSession();
}
```

#### 方案 2：LocalStorage 持久化

用于在会话过期后仍能保持的客户端持久化：

```javascript
// resources/js/filter-persistence.js
document.addEventListener('livewire:init', () => {
    const storageKey = 'filament-table-filters';

    // 在更改时保存筛选器
    Livewire.hook('message.processed', (message, component) => {
        if (component.fingerprint.name.includes('ListRecords')) {
            const filters = component.serverMemo.data.tableFilters;
            localStorage.setItem(storageKey, JSON.stringify(filters));
        }
    });

    // 页面加载时恢复筛选器
    Livewire.hook('component.initialized', (component) => {
        if (component.fingerprint.name.includes('ListRecords')) {
            const savedFilters = localStorage.getItem(storageKey);
            if (savedFilters) {
                component.call('setTableFilters', JSON.parse(savedFilters));
            }
        }
    });
});
```

#### 方案 3：数据库持久化

用于用户特定的筛选器偏好：

```php
// 创建用户偏好表/模型
Schema::create('user_filter_preferences', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('resource');
    $table->json('filters');
    $table->timestamps();
});

// 在您的 Resource 中
public function mount(): void
{
    parent::mount();

    $savedFilters = UserFilterPreference::where('user_id', auth()->id())
        ->where('resource', static::class)
        ->first();

    if ($savedFilters) {
        $this->tableFilters = $savedFilters->filters;
    }
}

public function updatedTableFilters(): void
{
    UserFilterPreference::updateOrCreate(
        ['user_id' => auth()->id(), 'resource' => static::class],
        ['filters' => $this->tableFilters]
    );
}
```

---

## URL 查询参数同步

### 当前限制

筛选器不会更新浏览器 URL，使得无法收藏或分享已筛选的视图。

### 推荐实现

#### 方案 1：Livewire URL 同步

```php
use Livewire\Attributes\Url;

class ListPosts extends ListRecords
{
    #[Url]
    public array $tableFilters = [];

    #[Url]
    public ?string $tableSearch = null;

    #[Url]
    public ?string $tableSortColumn = null;

    #[Url]
    public ?string $tableSortDirection = null;
}
```

#### 方案 2：自定义 URL 同步 Trait

创建可复用的 trait：

```php
<?php

namespace App\Concerns;

use Livewire\Attributes\Url;

trait SyncsFiltersToUrl
{
    #[Url(except: [])]
    public array $tableFilters = [];

    public function getFilterQueryString(): array
    {
        return collect($this->tableFilters)
            ->filter(fn ($value) => !empty($value))
            ->mapWithKeys(fn ($value, $key) => ["filter[{$key}]" => $value])
            ->toArray();
    }

    public function applyFiltersFromUrl(): void
    {
        $filters = request()->query('filter', []);

        foreach ($filters as $key => $value) {
            $this->tableFilters[$key] = $value;
        }
    }

    public function mount(): void
    {
        parent::mount();
        $this->applyFiltersFromUrl();
    }
}
```

使用方法：

```php
class ListPosts extends ListRecords
{
    use SyncsFiltersToUrl;

    // ...
}
```

---

## 级联筛选器依赖

### 当前限制

筛选器选项不能依赖于其他筛选器的值。例如，无法实现 国家 → 省份 → 城市 的级联。

### 推荐实现

#### 使用 Livewire 响应式属性

```php
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Tables\Filters\Filter;

Filter::make('location')
    ->form([
        Select::make('country_id')
            ->label('国家')
            ->options(Country::pluck('name', 'id'))
            ->live()
            ->afterStateUpdated(fn (callable $set) => $set('state_id', null)),

        Select::make('state_id')
            ->label('省份')
            ->options(function (Get $get) {
                $countryId = $get('country_id');

                if (!$countryId) {
                    return [];
                }

                return State::where('country_id', $countryId)
                    ->pluck('name', 'id');
            })
            ->live()
            ->afterStateUpdated(fn (callable $set) => $set('city_id', null)),

        Select::make('city_id')
            ->label('城市')
            ->options(function (Get $get) {
                $stateId = $get('state_id');

                if (!$stateId) {
                    return [];
                }

                return City::where('state_id', $stateId)
                    ->pluck('name', 'id');
            }),
    ])
    ->query(function (Builder $query, array $data): Builder {
        return $query
            ->when($data['country_id'], fn ($q, $v) => $q->where('country_id', $v))
            ->when($data['state_id'], fn ($q, $v) => $q->where('state_id', $v))
            ->when($data['city_id'], fn ($q, $v) => $q->where('city_id', $v));
    });
```

#### 创建 CascadingSelectFilter 类

```php
<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class CascadingSelectFilter extends Filter
{
    protected array $levels = [];

    /**
     * 添加一个级联层级。
     *
     * @param string $name 字段名
     * @param string $label 显示标签
     * @param string $model 模型类
     * @param string|null $parentField 父字段名（根级为 null）
     * @param string $foreignKey 外键列
     * @param string $titleColumn 显示列
     */
    public function addLevel(
        string $name,
        string $label,
        string $model,
        ?string $parentField = null,
        string $foreignKey = 'parent_id',
        string $titleColumn = 'name'
    ): static {
        $this->levels[] = [
            'name' => $name,
            'label' => $label,
            'model' => $model,
            'parentField' => $parentField,
            'foreignKey' => $foreignKey,
            'titleColumn' => $titleColumn,
        ];

        $this->rebuildForm();

        return $this;
    }

    protected function rebuildForm(): void
    {
        $fields = [];
        $previousField = null;

        foreach ($this->levels as $index => $level) {
            $field = Select::make($level['name'])
                ->label($level['label'])
                ->options(function (Get $get) use ($level, $previousField) {
                    $model = $level['model'];

                    if ($previousField) {
                        $parentValue = $get($previousField);

                        if (!$parentValue) {
                            return [];
                        }

                        return $model::where($level['foreignKey'], $parentValue)
                            ->pluck($level['titleColumn'], 'id');
                    }

                    return $model::pluck($level['titleColumn'], 'id');
                })
                ->native(false)
                ->live();

            // 当此字段更改时清除依赖字段
            $dependentFields = array_slice(
                array_column($this->levels, 'name'),
                $index + 1
            );

            if (!empty($dependentFields)) {
                $field->afterStateUpdated(function (callable $set) use ($dependentFields) {
                    foreach ($dependentFields as $fieldName) {
                        $set($fieldName, null);
                    }
                });
            }

            $fields[] = $field;
            $previousField = $level['name'];
        }

        $this->form($fields);
        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            foreach ($this->levels as $level) {
                $value = $data[$level['name']] ?? null;

                if ($value !== null && $value !== '') {
                    $query->where($level['name'], $value);
                }
            }

            return $query;
        });
    }
}
```

使用方法：

```php
CascadingSelectFilter::make('location')
    ->addLevel('country_id', '国家', Country::class)
    ->addLevel('state_id', '省份', State::class, 'country_id', 'country_id')
    ->addLevel('city_id', '城市', City::class, 'state_id', 'state_id');
```

---

## 无障碍访问改进

### 当前限制

缺少 ARIA 标签、role 属性和屏幕阅读器描述。

### 推荐实现

#### 1. 为筛选器组件添加 ARIA 标签

更新 Blade 模板：

```blade
{{-- 示例：modal-select.blade.php --}}
<div
    role="combobox"
    aria-haspopup="dialog"
    aria-expanded="false"
    aria-label="{{ $label }}"
    x-data="..."
>
    <button
        type="button"
        @click="openModal($event)"
        aria-describedby="filter-{{ $filterName }}-description"
        class="..."
    >
        <span class="sr-only">{{ __('打开 :label 的选择对话框', ['label' => $label]) }}</span>
        ...
    </button>

    <span id="filter-{{ $filterName }}-description" class="sr-only">
        {{ __('当前已选择：:count 项', ['count' => count($selected)]) }}
    </span>
</div>
```

#### 2. 键盘导航支持

```javascript
// 为弹窗添加键盘导航
x-data="{
    ...
    handleKeydown(event) {
        switch (event.key) {
            case 'Escape':
                this.cancel();
                break;
            case 'Enter':
                if (event.ctrlKey || event.metaKey) {
                    this.confirm();
                }
                break;
            case 'ArrowDown':
                this.focusNextRow();
                break;
            case 'ArrowUp':
                this.focusPreviousRow();
                break;
        }
    },
    focusNextRow() {
        // 实现代码
    },
    focusPreviousRow() {
        // 实现代码
    }
}"
@keydown="handleKeydown($event)"
```

#### 3. 屏幕阅读器公告

```javascript
// 向屏幕阅读器宣布筛选器更改
function announceToScreenReader(message) {
    const announcement = document.createElement('div');
    announcement.setAttribute('role', 'status');
    announcement.setAttribute('aria-live', 'polite');
    announcement.setAttribute('aria-atomic', 'true');
    announcement.className = 'sr-only';
    announcement.textContent = message;
    document.body.appendChild(announcement);

    setTimeout(() => announcement.remove(), 1000);
}

// 使用方法
updateSelection(selected, ...) {
    this.selected = selected;
    announceToScreenReader(`已选择 ${selected.length} 项`);
}
```

#### 4. 焦点管理

```javascript
// 在弹窗内限制焦点
x-trap.inert.noscroll="open"

// 弹窗关闭后将焦点返回到触发器
openModal(event) {
    this.triggerElement = event.target;
    this.open = true;
},
cancel() {
    this.open = false;
    this.$nextTick(() => {
        this.triggerElement?.focus();
    });
}
```

---

## 全面的测试覆盖

### 当前限制

只有架构测试存在。没有针对筛选器功能的单元测试或功能测试。

### 推荐测试结构

```
tests/
├── Feature/
│   ├── Filters/
│   │   ├── RangeFilterTest.php
│   │   ├── LikeFilterTest.php
│   │   ├── InFilterTest.php
│   │   ├── ComparisonFilterTest.php
│   │   ├── ScopeFilterTest.php
│   │   ├── ModalSelectFilterTest.php
│   │   ├── SelectTableFilterTest.php
│   │   ├── DateComponentFilterTest.php
│   │   └── HiddenFilterTest.php
│   └── Controllers/
│       └── ModalSelectControllerTest.php
└── Unit/
    ├── Concerns/
    │   └── HasRangeQueryTest.php
    └── Components/
        └── ModalSelectTableTest.php
```

### 示例测试

#### RangeFilterTest.php

```php
<?php

use Cooper\FilamentDcatFilters\Filters\RangeFilter;
use Illuminate\Database\Eloquent\Builder;

it('正确应用日期范围筛选器', function () {
    $filter = RangeFilter::make('created_at')->date();

    $query = Post::query();
    $data = ['from' => '2024-01-01', 'to' => '2024-12-31'];

    $result = $filter->apply($query, $data);

    expect($result->toSql())->toContain('between');
});

it('当 from 大于 to 时交换值', function () {
    $filter = RangeFilter::make('amount')->numeric();

    $query = Post::query();
    $data = ['from' => 100, 'to' => 50];

    $result = $filter->apply($query, $data);

    // 应该交换为 [50, 100]
    expect($result->getBindings())->toContain(50, 100);
});

it('将零视为有效值', function () {
    $filter = RangeFilter::make('quantity')->integer();

    $query = Product::query();
    $data = ['from' => 0, 'to' => 10];

    $result = $filter->apply($query, $data);

    expect($result->getBindings())->toContain(0);
});

it('正确处理空值', function () {
    $filter = RangeFilter::make('price')->numeric();

    $query = Product::query();
    $originalSql = $query->toSql();

    $result = $filter->apply($query, ['from' => null, 'to' => null]);

    expect($result->toSql())->toBe($originalSql);
});
```

#### LikeFilterTest.php

```php
<?php

use Cooper\FilamentDcatFilters\Filters\LikeFilter;

it('转义特殊的 LIKE 字符', function () {
    $filter = LikeFilter::make('title');

    $query = Post::query();
    $data = ['value' => '50%'];

    $result = $filter->apply($query, $data);

    expect($result->getBindings()[0])->toContain('\\%');
});

it('应用大小写不敏感搜索', function () {
    $filter = LikeFilter::make('name')->insensitive();

    $query = User::query();
    $data = ['value' => 'John'];

    $result = $filter->apply($query, $data);

    expect($result->toSql())->toContain('LOWER');
});

it('在正确位置应用通配符', function () {
    $filter = LikeFilter::make('email')->startsWith();

    $query = User::query();
    $data = ['value' => 'admin'];

    $result = $filter->apply($query, $data);

    expect($result->getBindings()[0])->toBe('admin%');
});
```

#### ModalSelectControllerTest.php

```php
<?php

use App\Models\User;
use Cooper\FilamentDcatFilters\Http\Controllers\ModalSelectController;

it('为有效模型返回标签', function () {
    $users = User::factory()->count(3)->create();

    $response = $this->postJson(route('filament-dcat-filters.fetch-labels'), [
        'model' => User::class,
        'ids' => $users->pluck('id')->toArray(),
        'column' => 'name',
        'keyColumn' => 'id',
    ]);

    $response->assertOk();
    $response->assertJsonCount(3, 'labels');
});

it('拒绝无效的模型类', function () {
    $response = $this->postJson(route('filament-dcat-filters.fetch-labels'), [
        'model' => 'InvalidClass',
        'ids' => [1],
        'column' => 'name',
    ]);

    $response->assertStatus(400);
});

it('拒绝未授权的模型访问', function () {
    config(['filament-dcat-filters.allowed_models' => [User::class]]);

    $response = $this->postJson(route('filament-dcat-filters.fetch-labels'), [
        'model' => Post::class,
        'ids' => [1],
        'column' => 'title',
    ]);

    $response->assertStatus(403);
});

it('遵守速率限制', function () {
    for ($i = 0; $i < 65; $i++) {
        $response = $this->postJson(route('filament-dcat-filters.fetch-labels'), [
            'model' => User::class,
            'ids' => [1],
            'column' => 'name',
        ]);
    }

    $response->assertStatus(429);
});
```

### 运行测试

```bash
# 运行所有测试
php artisan test

# 仅运行筛选器测试
php artisan test --filter=Filter

# 带覆盖率运行
php artisan test --coverage --min=80
```

---

## 实现优先级

| 功能 | 优先级 | 复杂度 | 影响度 |
|------|--------|--------|--------|
| 测试覆盖 | 高 | 中 | 高 |
| 无障碍访问 | 高 | 中 | 高 |
| URL 查询同步 | 中 | 低 | 中 |
| 筛选器持久化 | 中 | 低 | 中 |
| 重置所有按钮 | 低 | 低 | 低 |
| 级联筛选器 | 低 | 高 | 中 |

---

## 贡献

如果您想实现这些功能中的任何一个，请：

1. 打开一个 issue 讨论实现方案
2. 创建一个功能分支
3. 为新功能编写测试
4. 提交 pull request

我们欢迎贡献！
