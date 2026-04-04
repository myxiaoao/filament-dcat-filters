# CLAUDE.md

## Package Overview

Filament v4 插件包，将 Dcat Admin 的过滤器特性引入 Filament。基于 `spatie/laravel-package-tools`，命名空间 `Cooper\FilamentDcatFilters`。

## Commands

```bash
composer test              # vendor/bin/pest
composer test -- --filter=TestName  # 运行单个测试
composer format            # vendor/bin/pint
composer analyse           # vendor/bin/phpstan analyse
```

**注意：** 不要用 `php artisan test`，这是宿主应用的命令，不会运行包的测试套件。

## Project Structure

```
src/
├── Filters/          # 26 个 Filter 类（*Filter.php）
├── Concerns/         # 17 个 Trait（Has*.php / Persists* / Syncs*）
├── State/            # FilterStateDescriptor + StateType（状态协议）
├── Exceptions/       # UnsupportedDatabaseDriverException
├── Actions/          # ResetFiltersAction
├── Commands/         # MakeDcatFilterCommand
├── Components/       # ModalSelectTable Livewire 组件
├── Http/Controllers/ # ModalSelectFilter API 端点
├── Facades/
├── FilamentDcatFiltersServiceProvider.php
└── helpers.php
tests/Feature/        # 测试结构镜像 src/
config/               # filament-dcat-filters.php
docs/                 # en/, zh_CN/ 用户文档; plans/ 实施计划
```

## Key Architecture Decisions

- **跨切面 UI 行为在 Concerns/ trait 中**：label、prefix、placeholder、operator 等由 trait 统一控制，不在单个 Filter 类中
- **数据库驱动兼容**：`HasDatabaseDriver` trait 处理 MySQL/PostgreSQL/SQLite 的 SQL 差异
- **配置驱动默认值**：`config/filament-dcat-filters.php` 控制全局行为（inline_label、persistence、date_format 等）

## Debugging Guide

| 问题类型 | 先查 |
|---------|------|
| Label/prefix/placeholder 显示 | `src/Concerns/HasInlineLabel.php` |
| 操作符/查询逻辑 | `src/Concerns/HasOperator.php` |
| 列名解析 | `src/Concerns/HasColumnName.php` |
| Range 字段行为 | `src/Concerns/HasRangeQuery.php` |
| 数据库驱动差异 | `src/Concerns/HasDatabaseDriver.php` |
| Scope badge 计数 | `src/Concerns/HasScopeBadgeCounts.php` |
| 持久化（session/localStorage）| `src/Concerns/PersistsFiltersIn*.php` |
| 数据库驱动兼容/fail-fast | `src/Concerns/HasDatabaseDriver.php` |
| 状态协议/能力声明 | `src/State/FilterStateDescriptor.php` |

## Conventions

- Filter 类命名：`{Feature}Filter.php`，继承 Filament `Filter` 基类
- Concern trait 命名：`Has{Feature}.php`（行为）、`PersistsFiltersIn{Store}.php`（持久化）、`SyncsFiltersTo{Target}.php`（同步）
- 所有 Filter 使用 `make()` 静态工厂方法
- CI 使用 `preset: laravel` 的 Pint 配置

## Verification

完成修改前必须执行：

1. `composer test` — 所有测试通过
2. `composer format` — 代码风格一致
3. 如果修改了 trait，检查所有使用该 trait 的 Filter 类是否受影响
