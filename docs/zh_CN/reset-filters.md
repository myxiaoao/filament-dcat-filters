# 重置筛选器操作

重置筛选器操作提供了一种便捷的方式，可以一键清除所有活动的表格筛选器。

## 基本用法

将重置筛选器操作添加到表格的头部操作中：

```php
use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->filters([...])
        ->headerActions([
            ResetFiltersAction::make(),
        ]);
}
```

或者使用辅助函数：

```php
use function resetFiltersAction;

public function table(Table $table): Table
{
    return $table
        ->columns([...])
        ->filters([...])
        ->headerActions([
            resetFiltersAction(),
        ]);
}
```

## 功能特性

### 无筛选时自动隐藏

默认情况下，重置按钮仅在有活动筛选器时可见。您可以禁用此行为：

```php
ResetFiltersAction::make()
    ->autoHideWhenEmpty(false)
```

### 确认弹窗

您可以要求用户在重置筛选器前进行确认：

```php
ResetFiltersAction::make()
    ->withConfirmation()
```

这将显示一个带有可自定义文本的确认弹窗。

### 自定义样式

自定义按钮外观：

```php
ResetFiltersAction::make()
    ->label('清除全部')
    ->icon('heroicon-o-trash')
    ->color('danger')
    ->size('lg')
```

### 重置后回调

在筛选器重置后执行代码：

```php
ResetFiltersAction::make()
    ->afterReset(function ($livewire) {
        // 重置后的自定义逻辑
        $livewire->dispatch('filters-cleared');
    })
```

## 使用 Trait

使用 `HasResetFilters` trait 实现自动集成：

```php
use Cooper\FilamentDcatFilters\Concerns\HasResetFilters;
use Filament\Resources\Pages\ListRecords;

class ListPosts extends ListRecords
{
    use HasResetFilters;

    public function table(Table $table): Table
    {
        return $this->withResetFiltersAction(
            $table
                ->columns([...])
                ->filters([...])
        );
    }
}
```

## 事件

操作在重置筛选器后会分发 `filament-dcat-filters::filters-reset` 事件。您可以在 JavaScript 中监听此事件：

```javascript
document.addEventListener('livewire:init', () => {
    Livewire.on('filament-dcat-filters::filters-reset', () => {
        console.log('筛选器已重置');
    });
});
```

## 自定义

### 自定义标签

```php
ResetFiltersAction::make()
    ->label(__('清除所有筛选器'))
```

### 自定义图标

```php
use Filament\Support\Icons\Heroicon;

ResetFiltersAction::make()
    ->icon(Heroicon::OutlineTrash)
```

### 可见性控制

控制按钮何时可见：

```php
ResetFiltersAction::make()
    ->visible(fn ($livewire) => count($livewire->tableFilters) > 2)
```

## 完整示例

```php
use Cooper\FilamentDcatFilters\Actions\ResetFiltersAction;
use Cooper\FilamentDcatFilters\Filters\RangeFilter;
use Cooper\FilamentDcatFilters\Filters\ScopeFilter;
use Filament\Tables\Table;

public function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('title'),
            TextColumn::make('status'),
            TextColumn::make('created_at'),
        ])
        ->filters([
            ScopeFilter::forStatus('status', [
                'draft' => '草稿',
                'published' => '已发布',
            ]),
            RangeFilter::make('created_at')->date(),
        ])
        ->headerActions([
            ResetFiltersAction::make()
                ->withConfirmation()
                ->afterReset(function ($livewire) {
                    // 可选：通知用户
                    Notification::make()
                        ->title('筛选器已清除')
                        ->success()
                        ->send();
                }),
        ]);
}
```
