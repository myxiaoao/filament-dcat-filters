# 可访问性

本包遵循 WCAG 2.1 指南构建，确保所有用户都能有效地与筛选器交互。

## 功能特性

### ARIA 属性

所有筛选器组件都包含适当的 ARIA 属性，以支持屏幕阅读器：

- `role="combobox"` - 将筛选器输入标识为组合框
- `aria-haspopup="dialog"` - 表示元素会打开对话框
- `aria-expanded` - 动态反映展开/收起状态
- `aria-label` - 为交互元素提供描述性标签
- `aria-live="polite"` - 播报动态内容变化

### 键盘导航

筛选器支持完整的键盘导航：

| 按键 | 操作 |
|------|------|
| `Tab` | 在筛选器元素之间移动焦点 |
| `Enter` | 激活焦点筛选器/打开模态框 |
| `Space` | 激活焦点筛选器/打开模态框 |
| `Escape` | 关闭模态对话框 |
| `方向键` | 在下拉选项中导航 |

### 屏幕阅读器支持

本包为以下操作提供屏幕阅读器播报：

- 选择变更
- 筛选器应用
- 筛选器清除
- 加载状态
- 错误消息

```blade
{{-- 屏幕阅读器播报自动处理 --}}
<div role="status" aria-live="polite" aria-atomic="true" class="sr-only">
    选择已更新
</div>
```

## 翻译键

所有可访问性相关文本都支持翻译：

```php
// resources/lang/zh_CN/filament-dcat-filters.php
'accessibility.selection_updated' => '选择已更新',
'accessibility.selection_cleared' => '选择已清空',
'accessibility.change_selection' => '更改选择',
'accessibility.open_selection' => '打开选择对话框',
'accessibility.clear_selection' => '清空选择',
'accessibility.items_selected' => '项已选中',
'accessibility.no_selection' => '未选择任何项',
'accessibility.filter_applied' => '筛选器已应用',
'accessibility.filter_cleared' => '筛选器已清除',
'accessibility.loading' => '加载中',
'accessibility.error_occurred' => '发生错误',
```

## 焦点管理

本包自动处理焦点管理：

1. 打开模态框时，焦点移动到模态框内容
2. 关闭模态框时，焦点返回触发元素
3. 模态框内的焦点陷阱防止焦点逃逸

```javascript
// 焦点自动管理
openModal(event) {
    if (event) {
        this.triggerElement = event.target;
    }
    // ... 打开模态框
}

// 关闭时返回焦点（由 Filament 模态框处理）
```

## 最佳实践

### 自定义标签

始终为筛选器提供有意义的标签：

```php
ModalSelectFilter::make('author')
    ->label('选择作者')  // 清晰、描述性的标签
    ->modelClass(User::class)
    ->titleColumn('name');
```

### 描述性标题

使用描述性的对话框标题：

```php
ModalSelectFilter::make('category')
    ->dialogTitle('选择产品分类')
    ->modelClass(Category::class);
```

### 清晰反馈

本包提供视觉和听觉反馈：

- 带有 `aria-busy` 的加载指示器
- 带有 `role="alert"` 的错误消息
- 通过屏幕阅读器播报的成功确认

## 测试可访问性

您可以使用以下方式测试可访问性：

1. **纯键盘导航** - 不使用鼠标，用 Tab 键遍历所有筛选器
2. **屏幕阅读器** - 使用 VoiceOver (Mac)、NVDA 或 JAWS (Windows)
3. **浏览器开发工具** - 使用无障碍树检查器
4. **自动化工具** - axe DevTools、WAVE 或 Lighthouse

### 示例测试

```php
it('has proper ARIA attributes', function () {
    $html = view('filament-dcat-filters::filters.modal-select', [
        'filterName' => 'test',
        'component' => $mockComponent,
        // ... 其他属性
    ])->render();

    expect($html)
        ->toContain('role="combobox"')
        ->toContain('aria-haspopup="dialog"')
        ->toContain(':aria-expanded');
});
```

## 浏览器支持

可访问性功能在所有现代浏览器中都受支持：

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

屏幕阅读器支持：

- VoiceOver (macOS/iOS)
- NVDA (Windows)
- JAWS (Windows)
- TalkBack (Android)

## 贡献

如果您发现可访问性问题，请在 GitHub 上报告。我们非常重视可访问性，并优先修复可访问性相关的问题。
