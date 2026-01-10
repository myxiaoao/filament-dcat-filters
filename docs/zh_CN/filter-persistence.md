# 筛选器状态持久化

本包提供两种持久化筛选器状态的方法：服务端会话持久化和客户端 LocalStorage 持久化。

## 会话持久化

会话持久化将筛选器状态存储在服务器上，在同一会话内可以在页面刷新后保持。

### 基本用法

将 `PersistsFiltersInSession` trait 添加到您的 Livewire 组件：

```php
use Cooper\FilamentDcatFilters\Concerns\PersistsFiltersInSession;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use PersistsFiltersInSession;

    // 页面加载时筛选器会自动恢复
    // 筛选器变化时会自动保存
}
```

### 工作原理

1. 组件挂载时，从会话中恢复筛选器
2. 筛选器变化时（`updatedTableFilters`），保存到会话
3. 会话键对每个组件类是唯一的

### 手动控制

您可以手动控制持久化：

```php
// 保存当前筛选器
$this->saveFiltersToSession();

// 恢复筛选器
$this->restoreFiltersFromSession();

// 清除已保存的筛选器
$this->clearFiltersFromSession();
```

### 自定义会话键

重写方法来自定义会话键：

```php
protected function getFilterSessionKey(): string
{
    return 'my-custom-filters:' . auth()->id();
}
```

## LocalStorage 持久化

LocalStorage 持久化将筛选器状态存储在浏览器中，在会话过期后仍然保持。

### 基本用法

添加 `PersistsFiltersInLocalStorage` trait：

```php
use Cooper\FilamentDcatFilters\Concerns\PersistsFiltersInLocalStorage;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use PersistsFiltersInLocalStorage;

    public function mount(): void
    {
        parent::mount();

        // 初始化 LocalStorage 持久化
        $this->initLocalStoragePersistence();
    }
}
```

### 引入 JavaScript

将 JavaScript 文件添加到您的布局或 Filament 面板：

```html
<!-- 在您的布局中 -->
<script src="{{ asset('vendor/filament-dcat-filters/js/filter-persistence.js') }}"></script>
```

或者发布并在构建中导入：

```bash
php artisan vendor:publish --tag=filament-dcat-filters-assets
```

### JavaScript API

本包提供全局 JavaScript API：

```javascript
// 手动保存筛选器
FilamentDcatFilters.saveFilters('my-key', { status: 'active' });

// 加载筛选器
const filters = FilamentDcatFilters.loadFilters('my-key');

// 清除特定筛选器
FilamentDcatFilters.clearFilters('my-key');

// 清除所有筛选器持久化
FilamentDcatFilters.clearFilters();
```

## 配置

在 `config/filament-dcat-filters.php` 中配置持久化行为：

```php
'persistence' => [
    // 默认启用会话持久化
    'session_enabled' => true,

    // 会话键前缀
    'session_prefix' => 'filament-dcat-filters',

    // 默认启用 LocalStorage 持久化
    'local_storage_enabled' => false,

    // LocalStorage 键前缀
    'local_storage_prefix' => 'filament-dcat-filters',

    // 重置时自动清除持久化
    'clear_on_reset' => true,
],
```

## 组合使用两种方法

您可以同时使用会话和 LocalStorage 持久化：

```php
use Cooper\FilamentDcatFilters\Concerns\PersistsFiltersInSession;
use Cooper\FilamentDcatFilters\Concerns\PersistsFiltersInLocalStorage;

class ListPosts extends ListRecords
{
    use PersistsFiltersInSession;
    use PersistsFiltersInLocalStorage;

    public function mount(): void
    {
        parent::mount();

        // 会话优先，LocalStorage 作为后备
        if (empty($this->tableFilters)) {
            $this->initLocalStoragePersistence();
        }
    }
}
```

## 重置时清除持久化

使用 `ResetFiltersAction` 时，如果配置了，持久化会自动清除：

```php
ResetFiltersAction::make()
    ->afterReset(function ($livewire) {
        // 如果使用了 trait，会话会自动清除
        if (method_exists($livewire, 'clearFiltersFromSession')) {
            $livewire->clearFiltersFromSession();
        }
    });
```

## 用户特定持久化

对于用户特定的筛选器偏好，自定义会话键：

```php
protected function getFilterSessionKey(): string
{
    $userId = auth()->id() ?? 'guest';
    return "filament-dcat-filters:{$userId}:" . static::class;
}
```

## 事件

以下 Livewire 事件会被分发：

- `filament-dcat-filters::init-local-storage` - 初始化 LocalStorage 持久化
- `filament-dcat-filters::restore-from-local-storage` - 请求恢复筛选器
- `filament-dcat-filters::clear-local-storage` - 清除 LocalStorage
- `filament-dcat-filters::filters-reset` - 筛选器重置时分发

在 JavaScript 中监听这些事件：

```javascript
Livewire.on('filament-dcat-filters::filters-reset', () => {
    console.log('筛选器已重置');
});
```
