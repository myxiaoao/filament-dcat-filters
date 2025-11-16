# 使用示例

本文档提供了在 Resource 中使用所有 Filament Dcat Filters 的完整示例。

## 完整 Resource 示例

```php
<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Models\Post;
use Cooper\FilamentDcatFilters\Filters\{
    ScopeFilter,
    RangeFilter,
    LikeFilter,
    InFilter,
    ComparisonFilter,
    BetweenFilter,
    SelectTableFilter
};
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Textarea::make('content')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('views')
                    ->numeric()
                    ->default(0),
                Forms\Components\Select::make('author_id')
                    ->relationship('author', 'name')
                    ->searchable()
                    ->preload(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'draft' => 'gray',
                        'published' => 'success',
                        'archived' => 'danger',
                    }),
                Tables\Columns\TextColumn::make('views')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('author.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                // 1. Scope Filter - 使用标签页快速筛选状态
                ScopeFilter::make('status_filter')
                    ->label('Status')
                    ->scopes([
                        'all' => [
                            'label' => 'All Posts',
                            'default' => true,
                        ],
                        'published' => [
                            'label' => 'Published',
                            'query' => fn ($query) => $query->where('status', 'published'),
                        ],
                        'draft' => [
                            'label' => 'Drafts',
                            'query' => fn ($query) => $query->where('status', 'draft'),
                        ],
                        'archived' => [
                            'label' => 'Archived',
                            'query' => fn ($query) => $query->where('status', 'archived'),
                        ],
                    ])
                    ->columns(4),

                // 2. Date Scope Filter - 快速日期筛选
                ScopeFilter::forDates('created_at')
                    ->label('Created Date'),

                // 3. Range Filter - 日期范围
                RangeFilter::make('created_at')
                    ->label('Custom Date Range')
                    ->datetime(),

                // 4. Range Filter - 浏览量数值范围
                RangeFilter::make('views')
                    ->label('Views Count')
                    ->integer()
                    ->placeholders('Min views', 'Max views'),

                // 5. Like Filter - 按标题搜索
                LikeFilter::make('title')
                    ->label('Title Contains'),

                // 6. In Filter - 多选状态
                InFilter::make('status')
                    ->label('Status (Multiple)')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->multiple()
                    ->searchable(),

                // 7. Comparison Filter - 浏览量大于
                ComparisonFilter::make('views')
                    ->label('Minimum Views')
                    ->gt()
                    ->integer(),

                // 8. Between Filter - 价格范围（如果有 price 列）
                // BetweenFilter::make('price')
                //     ->label('Price Range'),

                // 9. SelectTable Filter - 从表格选择作者
                // 注意：需要正确的模型设置
                // SelectTableFilter::make('author_id')
                //     ->label('Author')
                //     ->model(\App\Models\User::class)
                //     ->tableColumns([
                //         Tables\Columns\TextColumn::make('name')->searchable(),
                //         Tables\Columns\TextColumn::make('email'),
                //     ])
                //     ->searchable(['name', 'email'])
                //     ->multiple(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
```

## 模型设置

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    protected $fillable = [
        'title',
        'content',
        'status',
        'views',
        'author_id',
    ];

    protected $casts = [
        'views' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }
}
```

## 数据库迁移

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->unsignedInteger('views')->default(0);
            $table->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

## 演示的主要功能

### 1. Scope Filter
- 使用标签页按钮快速筛选
- 默认 scope 选择
- 每个 scope 的自定义查询
- 多列布局

### 2. Range Filter
- 日期/日期时间范围筛选
- 数值/整数范围筛选
- 自定义占位符
- 自动处理单边范围

### 3. Like Filter
- 不区分大小写搜索
- 通配符定位（两端、开始、结束、无）
- 实时搜索支持

### 4. In Filter
- 多选支持
- 可搜索下拉框
- 复选框列表替代方案
- 自定义选项

### 5. Comparison Filter
- 大于/小于筛选
- 支持 >=、<=、=、!= 操作符
- 整数/数值输入类型
- 指示器中的清晰操作符标签

### 6. Between Filter
- 简化的数值范围筛选
- 整数聚焦的 RangeFilter 替代方案

### 7. SelectTable Filter
- 模态表格选择
- 搜索和分页支持
- 单选或多选
- 关联关系支持

## 使用技巧

1. **组合筛选器**：可以将多个筛选器组合在一起实现复杂的筛选
2. **性能**：ScopeFilter 对于用户经常需要的常见查询非常有用
3. **UX**：RangeFilter 为日期/数字范围提供比单独的 gt/lt 筛选器更好的用户体验
4. **搜索**：LikeFilter 适合文本搜索，InFilter 适合分类数据
5. **指示器**：所有筛选器在激活时会自动显示指示器

## 下一步

- 自定义筛选器标签和占位符
- 为常见查询添加更多 scopes
- 与 Filament 的原生筛选器结合
- 调整筛选器样式以匹配您的设计系统
