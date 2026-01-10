# URL 查询参数同步

将筛选器状态与浏览器 URL 参数同步，实现收藏和分享筛选视图的功能。

## 基本用法

将 `SyncsFiltersToUrl` trait 添加到您的 Livewire 组件：

```php
use Cooper\FilamentDcatFilters\Concerns\SyncsFiltersToUrl;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use SyncsFiltersToUrl;

    // 筛选器、搜索和排序将自动同步到 URL
}
```

## 同步内容

该 trait 将以下属性同步到 URL：

- `tableFilters` - 所有活动的筛选器值
- `tableSearch` - 搜索查询
- `tableSortColumn` - 排序列名
- `tableSortDirection` - 排序方向（asc/desc）

## URL 格式

URL 格式如下：

```
/posts?tableFilters[status][value]=published&tableSearch=hello&tableSortColumn=created_at&tableSortDirection=desc
```

## 历史记录模式

### 包含浏览器历史（默认）

使用 `SyncsFiltersToUrl` 将每次筛选器变化推送到浏览器历史：

```php
use Cooper\FilamentDcatFilters\Concerns\SyncsFiltersToUrl;

class ListPosts extends ListRecords
{
    use SyncsFiltersToUrl;
}
```

用户可以使用浏览器后退按钮浏览筛选器状态历史。

### 不包含浏览器历史

使用 `SyncsFiltersToUrlWithoutHistory` 更新 URL 但不创建历史记录：

```php
use Cooper\FilamentDcatFilters\Concerns\SyncsFiltersToUrlWithoutHistory;

class ListPosts extends ListRecords
{
    use SyncsFiltersToUrlWithoutHistory;
}
```

## 可分享链接

生成带有当前筛选器状态的可分享链接：

```php
// 在您的 Livewire 组件中
$shareUrl = $this->getShareableFilterUrl();

// 返回: https://example.com/posts?tableFilters[status][value]=published
```

## 获取查询字符串

以数组形式获取筛选器查询字符串：

```php
$query = $this->getFilterQueryString();

// 返回: [
//     'tableFilters' => ['status' => ['value' => 'published']],
//     'tableSearch' => 'hello',
// ]
```

## 重置 URL

清除所有 URL 参数：

```php
$this->resetUrlParameters();
```

这与 `ResetFiltersAction` 配合使用效果很好：

```php
ResetFiltersAction::make()
    ->afterReset(function ($livewire) {
        if (method_exists($livewire, 'resetUrlParameters')) {
            $livewire->resetUrlParameters();
        }
    });
```

## 自定义 URL 参数

如果需要自定义同步哪些属性，可以覆盖它们：

```php
use Livewire\Attributes\Url;

class ListPosts extends ListRecords
{
    // 只同步筛选器（不同步搜索或排序）
    #[Url(except: [])]
    public array $tableFilters = [];

    // 使用自定义 URL 参数名
    #[Url(as: 'q')]
    public ?string $tableSearch = null;

    // 不同步排序到 URL
    public ?string $tableSortColumn = null;
    public ?string $tableSortDirection = null;
}
```

## 选择性同步

创建自定义 trait 进行选择性同步：

```php
namespace App\Concerns;

use Livewire\Attributes\Url;

trait SyncsOnlyFiltersToUrl
{
    #[Url(except: [], history: true)]
    public array $tableFilters = [];
}
```

## 从 URL 初始化状态

当页面加载带有 URL 参数时，Livewire 会自动填充属性：

```php
// URL: /posts?tableFilters[status][value]=draft

class ListPosts extends ListRecords
{
    use SyncsFiltersToUrl;

    public function mount(): void
    {
        parent::mount();

        // $this->tableFilters 已从 URL 填充
        // ['status' => ['value' => 'draft']]
    }
}
```

## 安全注意事项

URL 参数是用户可控的。Filament 的筛选器系统已经验证和清理筛选器值，但请注意：

1. 筛选器值来自用户输入
2. 只有有效的筛选器配置才会被应用
3. 无效的筛选器数据会被 Filament 忽略

## 完整示例

```php
use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;
use Cooper\FilamentDcatFilters\Concerns\SyncsFiltersToUrl;
use Cooper\FilamentDcatFilters\Filters\ScopeFilter;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use SyncsFiltersToUrl;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('status'),
            ])
            ->filters([
                ScopeFilter::forStatus('status', [
                    'draft' => '草稿',
                    'published' => '已发布',
                ]),
            ])
            ->headerActions([
                ResetFiltersAction::make()
                    ->afterReset(fn ($livewire) => $livewire->resetUrlParameters()),
            ]);
    }
}
```
