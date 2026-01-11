# 文档结构

本文档记录了 filament-dcat-filters 包的文档结构。

## 文档组织

所有技术文档组织在 `docs/` 目录下，支持双语：

```
docs/
├── en/                           # 英文文档
│   ├── accessibility.md          # 无障碍功能（ARIA、键盘）
│   ├── advanced-features.md      # 高级功能和 API 支持
│   ├── cascading-filters.md      # 级联选择筛选器
│   ├── comparison.md             # 与 Dcat Admin 对比
│   ├── concerns-traits.md        # 筛选器 traits 文档
│   ├── date-component-filter.md  # 日期组件筛选器
│   ├── demo-guide.md             # 交互式演示指南
│   ├── documentation-structure.md # 本文件
│   ├── feature-analysis.md       # 功能分析和状态
│   ├── filter-group.md           # 筛选器组 AND/OR 逻辑
│   ├── filter-persistence.md     # 筛选器状态持久化
│   ├── find-in-set-filter.md     # FindInSet 筛选器
│   ├── future-improvements.md    # 实现状态和未来计划
│   ├── geo-location-filter.md    # 地理位置筛选器
│   ├── input-mask-filter.md      # 输入掩码筛选器
│   ├── json-filter.md            # JSON 列筛选器
│   ├── modal-select-filter.md    # 模态选择筛选器
│   ├── package-structure.md      # 包架构
│   ├── quick-filters.md          # 快速筛选器（Like, In, Comparison）
│   ├── range-filter.md           # 范围筛选器
│   ├── regex-filter.md           # 正则表达式筛选器
│   ├── reset-filters.md          # 重置筛选器功能
│   ├── scope-filter.md           # Scope 筛选器
│   ├── select-table-filter.md    # SelectTable 筛选器
│   ├── url-sync.md               # URL 查询同步
│   └── usage-example.md          # 完整使用示例
│
└── zh_CN/                        # 中文文档（与 en/ 镜像）
    ├── accessibility.md
    ├── advanced-features.md
    ├── cascading-filters.md
    ├── comparison.md
    ├── concerns-traits.md
    ├── date-component-filter.md
    ├── demo-guide.md
    ├── documentation-structure.md
    ├── feature-analysis.md
    ├── filter-group.md
    ├── filter-persistence.md
    ├── find-in-set-filter.md
    ├── future-improvements.md
    ├── geo-location-filter.md
    ├── input-mask-filter.md
    ├── json-filter.md
    ├── modal-select-filter.md
    ├── package-structure.md
    ├── quick-filters.md
    ├── range-filter.md
    ├── regex-filter.md
    ├── reset-filters.md
    ├── scope-filter.md
    ├── select-table-filter.md
    ├── url-sync.md
    └── usage-example.md
```

## 根目录文件

```
packages/filament-dcat-filters/
├── README.md           # 项目介绍和快速开始（英文）
├── README_CN.md        # 中文版 README
├── CHANGELOG.md        # 版本更新日志
├── CONTRIBUTING.md     # 贡献指南
└── LICENSE             # MIT 许可证
```

## 文档分类

### 核心筛选器
| 文档 | 描述 |
|------|------|
| `scope-filter.md` | Tab 样式快速筛选器（带徽章） |
| `range-filter.md` | 日期/数字范围筛选 |
| `date-component-filter.md` | 年/月/日筛选 |
| `select-table-filter.md` | 模态表格选择器 |
| `modal-select-filter.md` | Dcat Admin 风格模态框 |
| `quick-filters.md` | LikeFilter, InFilter, ComparisonFilter, BetweenFilter |

### 专用筛选器
| 文档 | 描述 |
|------|------|
| `json-filter.md` | JSON/JSONB 列筛选 |
| `find-in-set-filter.md` | 逗号分隔值筛选 |
| `regex-filter.md` | 正则表达式模式 |
| `input-mask-filter.md` | 带掩码的格式化输入 |
| `geo-location-filter.md` | 地理位置临近筛选 |
| `filter-group.md` | AND/OR 筛选器组合 |

### 高级功能
| 文档 | 描述 |
|------|------|
| `reset-filters.md` | 一键重置功能 |
| `filter-persistence.md` | 基于会话的筛选器记忆 |
| `url-sync.md` | 可分享的筛选器 URL |
| `cascading-filters.md` | 依赖下拉框 |
| `accessibility.md` | ARIA 标签和键盘支持 |
| `concerns-traits.md` | 预设、徽章计数、导出/导入 |
| `advanced-features.md` | API 支持、HiddenFilter |

### 指南和参考
| 文档 | 描述 |
|------|------|
| `usage-example.md` | 完整工作示例 |
| `demo-guide.md` | 交互式演示 |
| `comparison.md` | 与 Dcat Admin 功能对比 |
| `package-structure.md` | 包架构 |
| `feature-analysis.md` | 实现状态 |
| `future-improvements.md` | 路线图和计划功能 |

## CI/CD 配置

```
.github/
├── FUNDING.yml                   # GitHub Sponsors 配置
├── dependabot.yml                # 依赖自动更新
├── ISSUE_TEMPLATE/
│   ├── bug.yml                   # Bug 报告模板
│   └── config.yml                # Issue 模板配置
└── workflows/
    ├── dependabot-auto-merge.yml # 自动合并 Dependabot PR
    ├── pint.yml                  # Laravel Pint 代码风格检查
    ├── run-tests.yml             # 自动化测试
    └── update-changelog.yml      # 自动更新 CHANGELOG
```

## 文档标准

1. **双语支持**：所有文档提供英文和中文版本
2. **代码示例**：每个功能包含可工作的代码片段
3. **API 参考**：完整的方法签名和参数
4. **使用场景**：常见场景的实际示例
5. **版本说明**：清晰的兼容性信息

## 测试覆盖

- **461 个测试**，**630 个断言**
- 所有筛选器的功能测试
- 核心功能的单元测试
- 代码质量的架构测试

## 参考资料

- [Laravel Package Development](https://laravel.com/docs/packages)
- [Filament Plugin Development](https://filamentphp.com/docs/support/plugins)
- [Spatie Package Tools](https://github.com/spatie/laravel-package-tools)
