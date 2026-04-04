# 过滤器能力矩阵

> 由 `php artisan dcat-filters:matrix` 自动生成

| 过滤器 | 状态类型 | 字段 | 关联关系 | 多选 | 指示器 | 数据库支持 | 限制 |
|--------|---------|------|:-------:|:----:|:------:|-----------|------|
| AggregateFilter | single | value | — | — | ✓ | mysql, pgsql, sqlite | — |
| BetweenFilter | range | from, to | — | — | ✓ | mysql, pgsql, sqlite | — |
| BooleanFilter | single | value | — | — | ✓ | mysql, pgsql, sqlite | — |
| CascadingSelectFilter | composite | 动态（按级别） | — | — | ✓ | mysql, pgsql, sqlite | — |
| ColumnCompareFilter | toggle/keyed | enabled/operator | — | — | ✓ | mysql, pgsql, sqlite | — |
| ComparisonFilter | single | value | — | — | ✓ | mysql, pgsql, sqlite | — |
| DateComponentFilter | single | value | — | — | ✓ | mysql, pgsql, sqlite | — |
| EnumFilter | single/multiple | value/values | — | ✓（可选） | ✓ | mysql, pgsql, sqlite | — |
| ExistsFilter | toggle/single | value | — | — | ✓ | mysql, pgsql, sqlite | — |
| FilterGroup | composite | 动态（按子过滤器） | — | — | ✓ | mysql, pgsql, sqlite | — |
| FindInSetFilter | single | value | — | ✓ | ✓ | mysql, pgsql | SQLite 无原生 FIND_IN_SET |
| FullTextFilter | keyed | search | — | — | ✓ | mysql, pgsql | SQLite 降级为 LIKE 搜索 |
| GeoLocationFilter | composite | latitude, longitude, radius | — | — | ✓ | mysql, pgsql, sqlite | 需要三角函数支持 |
| HiddenFilter | single | value | — | — | — | mysql, pgsql, sqlite | — |
| InFilter | single/multiple | value/values | ✓ | ✓（可选） | ✓ | mysql, pgsql, sqlite | — |
| InputMaskFilter | single | value | — | — | ✓ | mysql, pgsql, sqlite | — |
| JsonFilter | single | value | — | — | ✓ | mysql, pgsql, sqlite | — |
| LikeFilter | single | value | ✓ | — | ✓ | mysql, pgsql, sqlite | — |
| ModalSelectFilter | single | value | ✓ | ✓ | ✓ | mysql, pgsql, sqlite | — |
| NullFilter | single | value | — | — | ✓ | mysql, pgsql, sqlite | — |
| RangeFilter | range | from, to | — | — | ✓ | mysql, pgsql, sqlite | — |
| RegexFilter | keyed/toggle | pattern/enabled | — | — | ✓ | mysql, pgsql | — |
| RelativeDateFilter | keyed | preset | — | — | ✓ | mysql, pgsql, sqlite | — |
| ScopeFilter | keyed | scope | — | — | ✓ | mysql, pgsql, sqlite | — |
| SelectTableFilter | single/multiple | value/values | ✓ | ✓（可选） | ✓ | mysql, pgsql, sqlite | — |
| SoftDeleteFilter | keyed/toggle | trashed | — | — | ✓ | mysql, pgsql, sqlite | — |

## 状态类型说明

| 类型 | 含义 | 代表过滤器 |
|------|------|-----------|
| `single` | 单值字段 `value` | LikeFilter, BooleanFilter, ComparisonFilter |
| `multiple` | 多值字段 `values`（数组） | InFilter(multiple), EnumFilter(multiple) |
| `range` | 范围字段 `from/to` | RangeFilter, BetweenFilter |
| `keyed` | 自定义键名字段 | ScopeFilter(`scope`), FullTextFilter(`search`) |
| `toggle` | 开关字段 `enabled` | RegexFilter(固定模式) |
| `composite` | 多个独立字段 | GeoLocationFilter, CascadingSelectFilter |

## 能力说明

- **关联关系**：支持通过 `relationship()` 方法进行 `whereHas` 查询
- **多选**：支持通过 `multiple()` 方法启用多值选择
- **指示器**：激活时在过滤栏显示可移除的状态标签
- **数据库支持**：已验证兼容的数据库驱动
