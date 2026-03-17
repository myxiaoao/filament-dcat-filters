# PHP / Filament 约定

## Filter 类

- 所有 Filter 类放在 `src/Filters/`，命名为 `{Feature}Filter.php`
- 必须继承 Filament 的 `Filter` 基类或其子类
- 使用 `make()` 静态工厂方法实例化
- 通过组合 `src/Concerns/` 中的 trait 获得跨切面能力，不在 Filter 类中重复实现 UI 行为

## Concern Traits

- 放在 `src/Concerns/`，命名规则：
  - `Has{Feature}.php` — 行为能力（HasInlineLabel, HasOperator, HasColumnName...）
  - `PersistsFiltersIn{Store}.php` — 持久化（Session, LocalStorage）
  - `SyncsFiltersTo{Target}.php` — 同步（Url, UrlWithoutHistory）
- Trait 是跨切面 UI 行为的唯一来源，修改 label/prefix/placeholder/operator 等行为时改 trait，不改 Filter

## 数据库兼容

- 涉及数据库方言差异（LIKE/ILIKE, REGEXP, FIND_IN_SET, JSON 查询）时，使用 `HasDatabaseDriver` trait
- 不要硬编码 MySQL 特有语法

## 测试

- 每个 Filter 对应 `tests/Feature/Filters/{FilterName}Test.php`
- 每个 Concern 对应 `tests/Feature/Concerns/{TraitName}Test.php`
- 使用 Pest + Orchestra Testbench，运行 `composer test`
- 新增或修改 Filter/Trait 必须有对应测试

## 配置

- 可配置的默认值放 `config/filament-dcat-filters.php`，不硬编码
- 通过 `config('filament-dcat-filters.xxx')` 读取

## 代码风格

- 遵循 Laravel preset（Pint），提交前运行 `composer format`
- 命名空间根：`Cooper\FilamentDcatFilters`
