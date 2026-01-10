<div align="center">

# Filament Dcat Filters

**将 Dcat Admin 强大的过滤器功能带到 Filament**

基于 PHP 8.3+ 构建，适用于 Laravel 12 和 Filament v4

[![Latest Version on Packagist](https://img.shields.io/packagist/v/cooper/filament-dcat-filters.svg?style=flat-square)](https://packagist.org/packages/cooper/filament-dcat-filters)
[![Total Downloads](https://img.shields.io/packagist/dt/cooper/filament-dcat-filters.svg?style=flat-square)](https://packagist.org/packages/cooper/filament-dcat-filters)
[![run-tests](https://github.com/myxiaoao/filament-dcat-filters/actions/workflows/run-tests.yml/badge.svg)](https://github.com/myxiaoao/filament-dcat-filters/actions/workflows/run-tests.yml)
[![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)
[![PHP](https://img.shields.io/badge/php-8.3+-purple.svg)](https://www.php.net)
[![Laravel](https://img.shields.io/badge/laravel-12.x-red.svg)](https://laravel.com)
[![Filament](https://img.shields.io/badge/filament-4.x-orange.svg)](https://filamentphp.com)

<img src="./art/filters.png" alt="Filament Dcat Filters 截图" width="800">

---

一套现代化的增强过滤器集合，灵感来自 [Dcat Admin](https://github.com/jqhph/dcat-admin)，将直观的 UI 组件与强大的过滤功能相结合，为 [Filament](https://filamentphp.com) 后台管理面板提供卓越的用户体验。

[English Documentation](README.md) | [中文文档](#功能特性)

</div>

## 功能特性

### 核心过滤器
- 🎯 **Scope 过滤器** - 选项卡风格的快速过滤器，适合常见查询
- 📊 **范围过滤器** - 简化的日期/数字范围过滤（仅需 3 行代码！）
- 📅 **日期组件过滤器** - 分别按年、月、日过滤
- 🔍 **SelectTable 过滤器** - 带搜索和分页的模态框表格选择器
- 🎭 **模态选择过滤器** - Dcat Admin 风格的完整表格显示模态框
- 🔢 **Between 过滤器** - 数值范围过滤快捷方式
- 🙈 **隐藏过滤器** - 基于 URL 参数的无界面过滤

### 快速过滤器
- ⚡ **LIKE 过滤器** - 文本搜索，支持通配符控制（支持 NOT LIKE）
- 📋 **IN 过滤器** - 多值选择（支持 NOT IN）
- 🔢 **比较过滤器** - 比较运算符（>、<、>=、<=、=、!=）

### 高级功能
- 🔄 **重置所有过滤器** - 一键重置所有活动过滤器
- 💾 **过滤器状态持久化** - 跨会话记住过滤器状态
- 🔗 **URL 查询参数同步** - 可分享的过滤器 URL，无需页面刷新
- 🔗 **级联选择过滤器** - 动态依赖下拉菜单
- ♿ **无障碍支持** - ARIA 标签和键盘导航

### 其他特性
- 🎨 **高度可定制** - 每个过滤器都有丰富的自定义选项
- 📱 **移动端友好** - 响应式设计，适配所有屏幕尺寸
- 🌐 **双语文档** - 完整的中英文文档
- ✅ **全面测试** - 200+ 测试用例的全面覆盖

## 版本兼容性

| Filament | Filament Dcat Filters | PHP    | Laravel |
|----------|----------------------|--------|---------|
| 4.x      | 1.x                  | ^8.3   | ^12.0   |

## 安装

通过 composer 安装包：

```bash
composer require cooper/filament-dcat-filters
```

可选：发布配置文件

```bash
php artisan vendor:publish --tag="filament-dcat-filters-config"
```

可选：发布视图文件

```bash
php artisan vendor:publish --tag="filament-dcat-filters-views"
```

## 快速开始

### Scope 过滤器

选项卡风格的快速过滤器：

```php
use Cooper\FilamentDcatFilters\Filters\ScopeFilter;

ScopeFilter::make('status')
    ->scopes([
        'all' => '全部',
        'active' => '已激活',
        'inactive' => '未激活',
    ])
```

**[查看详细文档 →](docs/zh_CN/scope-filter.md)**

### 范围过滤器

简化的日期/数字范围过滤：

```php
use Cooper\FilamentDcatFilters\Filters\RangeFilter;

RangeFilter::make('created_at')->datetime()
```

**[查看详细文档 →](docs/zh_CN/range-filter.md)**

### SelectTable 过滤器

带搜索和分页的模态框表格选择器：

```php
use Cooper\FilamentDcatFilters\Filters\SelectTableFilter;

SelectTableFilter::make('user_id')
    ->relationship('user', 'name')
    ->multiple()
```

**[查看详细文档 →](docs/zh_CN/select-table-filter.md)**

### 日期组件过滤器

按年、月、日组件过滤：

```php
use Cooper\FilamentDcatFilters\Filters\DateComponentFilter;

DateComponentFilter::make('created_at')->year()
DateComponentFilter::make('birth_date')->month()
DateComponentFilter::make('published_at')->day()
```

**[查看详细文档 →](docs/zh_CN/date-component-filter.md)**

### 模态选择过滤器

Dcat Admin 风格的完整表格显示模态框：

```php
use Cooper\FilamentDcatFilters\Filters\ModalSelectFilter;

ModalSelectFilter::make('user_id')
    ->model(User::class, 'name', 'id')
    ->dialogTitle('选择用户')
    ->displayColumns(['id' => 'ID', 'name' => '姓名', 'email' => '邮箱'])
    ->searchable(['name', 'email'])
    ->multiple()
```

**[查看详细文档 →](docs/zh_CN/modal-select-filter.md)**

### 快速过滤器

常见操作的内置过滤器：

```php
use Cooper\FilamentDcatFilters\Filters\{LikeFilter, InFilter, ComparisonFilter, BetweenFilter};

// LIKE 搜索（支持 NOT LIKE）
LikeFilter::make('title'),
LikeFilter::make('spam_keywords')->notLike(), // 排除匹配

// IN 数组（支持 NOT IN）
InFilter::make('category_id')
    ->options(Category::pluck('name', 'id')->toArray()),
InFilter::make('blocked_users')->notIn(), // 排除选中项

// 比较运算 (>, <, =, >=, <=, !=)
ComparisonFilter::make('views')->gte()->label('最小浏览量'),

// Between（数值范围）
BetweenFilter::make('price')->label('价格范围'),
```

**[查看详细文档 →](docs/zh_CN/quick-filters.md)**

### 隐藏过滤器

基于 URL 参数的过滤（无界面）：

```php
use Cooper\FilamentDcatFilters\Filters\HiddenFilter;

// 按租户预过滤
HiddenFilter::make('tenant_id')
    ->default(auth()->user()->tenant_id)
    ->eq()
```

**[查看详细文档 →](docs/zh_CN/advanced-features.md#hiddenfilter-使用说明)**

### 重置所有过滤器

添加一键重置按钮：

```php
use Cooper\FilamentDcatFilters\Concerns\HasResetFilters;

class ListUsers extends ListRecords
{
    use HasResetFilters;

    protected function getHeaderActions(): array
    {
        return [
            $this->getResetFiltersAction(),
        ];
    }
}
```

**[查看详细文档 →](docs/zh_CN/reset-filters.md)**

### 过滤器状态持久化

跨会话记住过滤器状态：

```php
use Cooper\FilamentDcatFilters\Concerns\HasFilterPersistence;

class ListUsers extends ListRecords
{
    use HasFilterPersistence;

    protected string $filterPersistenceKey = 'users-list-filters';
}
```

**[查看详细文档 →](docs/zh_CN/filter-persistence.md)**

### URL 查询参数同步

可分享的过滤器 URL：

```php
use Cooper\FilamentDcatFilters\Concerns\SyncsFiltersToUrlWithoutHistory;

class ListUsers extends ListRecords
{
    use SyncsFiltersToUrlWithoutHistory;
}
```

**[查看详细文档 →](docs/zh_CN/url-sync.md)**

### 级联选择过滤器

动态依赖下拉菜单：

```php
use Cooper\FilamentDcatFilters\Filters\CascadingSelectFilter;

CascadingSelectFilter::make('location')
    ->levels([
        'country' => [
            'label' => '国家',
            'options' => fn () => Country::pluck('name', 'id'),
        ],
        'state' => [
            'label' => '省份',
            'options' => fn ($country) => State::where('country_id', $country)->pluck('name', 'id'),
            'dependsOn' => 'country',
        ],
        'city' => [
            'label' => '城市',
            'options' => fn ($state) => City::where('state_id', $state)->pluck('name', 'id'),
            'dependsOn' => 'state',
        ],
    ])
```

**[查看详细文档 →](docs/zh_CN/cascading-filters.md)**

## 文档

### 核心过滤器
- 📖 [Scope 过滤器](docs/zh_CN/scope-filter.md) - 选项卡风格快速过滤
- 📖 [范围过滤器](docs/zh_CN/range-filter.md) - 日期/数字范围过滤
- 📖 [日期组件过滤器](docs/zh_CN/date-component-filter.md) - 年/月/日过滤
- 📖 [SelectTable 过滤器](docs/zh_CN/select-table-filter.md) - 模态框表格选择器
- 📖 [模态选择过滤器](docs/zh_CN/modal-select-filter.md) - Dcat Admin 风格的模态表格选择器
- 📖 [快速过滤器](docs/zh_CN/quick-filters.md) - LIKE、IN、GT、LT、BETWEEN 过滤器

### 高级功能
- 📖 [重置所有过滤器](docs/zh_CN/reset-filters.md) - 一键重置功能
- 📖 [过滤器状态持久化](docs/zh_CN/filter-persistence.md) - 基于会话的过滤器记忆
- 📖 [URL 查询参数同步](docs/zh_CN/url-sync.md) - 可分享的过滤器 URL
- 📖 [级联选择过滤器](docs/zh_CN/cascading-filters.md) - 动态依赖下拉菜单
- 📖 [无障碍访问](docs/zh_CN/accessibility.md) - ARIA 标签和键盘支持
- 📖 [高级功能](docs/zh_CN/advanced-features.md) - API 支持、InputMask、FindInSet、Hidden 过滤器

### 指南和参考
- 📖 [使用示例](docs/zh_CN/usage-example.md) - 完整的工作示例
- 📖 [演示指南](docs/zh_CN/demo-guide.md) - 交互式演示
- 📖 [与 Dcat Admin 对比](docs/zh_CN/comparison.md) - 功能对比
- 📖 [包结构](docs/zh_CN/package-structure.md) - 包架构
- 📖 [文档结构](docs/zh_CN/documentation-structure.md) - 文档组织
- 📖 [未来改进](docs/zh_CN/future-improvements.md) - 路线图和计划功能

## Facade 用法

也可以使用 Facade 快速访问：

```php
use Cooper\FilamentDcatFilters\Facades\FilamentDcatFilters;

FilamentDcatFilters::scopeFilter('status')->scopes([...]);
FilamentDcatFilters::rangeFilter('created_at')->datetime();
```

## 测试

```bash
composer test
```

## 代码质量

```bash
# 格式化代码
composer format

# 静态分析
composer analyse
```

## 更新日志

查看 [CHANGELOG](CHANGELOG.md) 了解最近的更新内容。

## 贡献

查看 [CONTRIBUTING](CONTRIBUTING.md) 了解详细信息。

## 安全漏洞

如果您发现任何安全相关的问题，请发送邮件至 `myxiaoao@gmail.com`。

## 贡献者

- [Cooper](https://github.com/myxiaoao)
- 灵感来自 [Dcat Admin](https://github.com/jqhph/dcat-admin)
- [所有贡献者](../../contributors)

## 开源协议

MIT 协议。查看 [License File](LICENSE) 了解更多信息。
