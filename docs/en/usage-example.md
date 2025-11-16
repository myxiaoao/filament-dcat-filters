# Usage Example

This document provides a complete example of using all Filament Dcat Filters in a Resource.

## Complete Resource Example

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
                // 1. Scope Filter - Quick status filtering with tabs
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

                // 2. Date Scope Filter - Quick date filtering
                ScopeFilter::forDates('created_at')
                    ->label('Created Date'),

                // 3. Range Filter - Date range
                RangeFilter::make('created_at')
                    ->label('Custom Date Range')
                    ->datetime(),

                // 4. Range Filter - Numeric range for views
                RangeFilter::make('views')
                    ->label('Views Count')
                    ->integer()
                    ->placeholders('Min views', 'Max views'),

                // 5. Like Filter - Search by title
                LikeFilter::make('title')
                    ->label('Title Contains'),

                // 6. In Filter - Multiple status selection
                InFilter::make('status')
                    ->label('Status (Multiple)')
                    ->options([
                        'draft' => 'Draft',
                        'published' => 'Published',
                        'archived' => 'Archived',
                    ])
                    ->multiple()
                    ->searchable(),

                // 7. Comparison Filter - Views greater than
                ComparisonFilter::make('views')
                    ->label('Minimum Views')
                    ->gt()
                    ->integer(),

                // 8. Between Filter - Price range (if you have a price column)
                // BetweenFilter::make('price')
                //     ->label('Price Range'),

                // 9. SelectTable Filter - Select author from table
                // Note: This would require proper model setup
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

## Model Setup

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

## Migration

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

## Key Features Demonstrated

### 1. Scope Filter
- Quick filtering with tab-style buttons
- Default scope selection
- Custom query for each scope
- Multiple columns layout

### 2. Range Filter
- Date/datetime range filtering
- Numeric/integer range filtering
- Custom placeholders
- Automatic handling of single-sided ranges

### 3. Like Filter
- Case-insensitive search
- Wildcard positioning (both, start, end, none)
- Live search support

### 4. In Filter
- Multiple selection support
- Searchable dropdown
- Checkbox list alternative
- Custom options

### 5. Comparison Filter
- Greater than / less than filtering
- Support for >=, <=, =, != operators
- Integer/numeric input types
- Clear operator labels in indicators

### 6. Between Filter
- Simplified numeric range filtering
- Integer-focused alternative to RangeFilter

### 7. SelectTable Filter
- Modal table selection
- Search and pagination support
- Single or multiple selection
- Relationship support

## Tips

1. **Combining Filters**: You can use multiple filters together for complex filtering
2. **Performance**: ScopeFilter is great for common queries that users frequently need
3. **UX**: RangeFilter provides better UX for date/number ranges compared to separate gt/lt filters
4. **Search**: LikeFilter is perfect for text search, InFilter for categorical data
5. **Indicators**: All filters automatically show indicators when active

## Next Steps

- Customize filter labels and placeholders
- Add more scopes for common queries
- Combine with Filament's native filters
- Style the filters to match your design system
