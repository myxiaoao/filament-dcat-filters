<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasDatabaseDriver;
use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class FullTextFilter extends Filter
{
    use HasDatabaseDriver;
    use HasFilterState;
    use HasInlineLabel;

    protected array $searchColumns = [];

    protected int $minLength = 2;

    protected int $debounce = 300;

    protected bool $useFullText = false;

    protected ?string $placeholder = null;

    protected function describeState(): FilterStateDescriptor
    {
        return FilterStateDescriptor::make()
            ->fields(['search'])
            ->type(StateType::Keyed)
            ->capabilities(['indicator'])
            ->databaseSupport(['mysql', 'pgsql'])
            ->degradedSupport(['sqlite'])
            ->limitations(['SQLite falls back to LIKE-based search']);
    }

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
        $this->configureForm();
    }

    /**
     * Set the columns to search across.
     */
    public function searchIn(array $columns): static
    {
        $this->searchColumns = $columns;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the minimum search length.
     */
    public function minLength(int $length): static
    {
        $this->minLength = $length;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the debounce delay in milliseconds.
     */
    public function debounce(int $ms): static
    {
        $this->debounce = $ms;
        $this->configureForm();

        return $this;
    }

    /**
     * Use MySQL FULLTEXT search (requires FULLTEXT index on columns).
     */
    public function fullText(bool $useFullText = true): static
    {
        $this->useFullText = $useFullText;
        $this->configureForm();

        return $this;
    }

    /**
     * Set custom placeholder text.
     */
    public function placeholder(string $placeholder): static
    {
        $this->placeholder = $placeholder;
        $this->configureForm();

        return $this;
    }

    /**
     * Configure form component.
     */
    protected function configureForm(): void
    {
        $placeholder = $this->placeholder ?? __('filament-dcat-filters::filament-dcat-filters.fulltext.placeholder');
        /** @phpstan-ignore nullCoalesce.expr (Filament's getLabel() can return null at runtime) */
        $labelResolver = fn (): string => $this->getLabel() ?? __('filament-dcat-filters::filament-dcat-filters.fulltext.label');

        $input = TextInput::make('search')
            ->label($labelResolver)
            ->placeholder($placeholder)
            ->minLength($this->minLength)
            ->debounce($this->debounce)
            ->columnSpanFull();

        $this->applyInlineLabel($input, $labelResolver);

        $this->form([$input]);

        $this->configureQuery();
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $search = trim($data['search'] ?? '');

            if (strlen($search) < $this->minLength) {
                return $query;
            }

            if (empty($this->searchColumns)) {
                return $query;
            }

            $this->assertDriverSupported($query);

            if ($this->useFullText) {
                return $this->applyFullTextWithRelations($query, $search);
            }

            return $this->applyLikeSearch($query, $search);
        });

        $this->indicateUsing(function (array $data): array {
            $search = trim($data['search'] ?? '');

            if (strlen($search) < $this->minLength) {
                return [];
            }

            /** @phpstan-ignore nullCoalesce.expr (Filament's getLabel() can return null at runtime) */
            $label = $this->getLabel() ?? __('filament-dcat-filters::filament-dcat-filters.fulltext.label');

            return [
                Indicator::make("{$label}: \"{$search}\"")
                    ->removeField('search'),
            ];
        });
    }

    /**
     * Apply LIKE-based search across columns.
     */
    protected function applyLikeSearch(Builder $query, string $search): Builder
    {
        $likeOp = $this->isPostgres($query) ? 'ILIKE' : 'LIKE';

        return $query->where(function (Builder $q) use ($search, $likeOp) {
            foreach ($this->searchColumns as $column) {
                if (str_contains($column, '.')) {
                    $this->applyRelationSearch($q, $column, $search, $likeOp);
                } else {
                    $wrapped = $q->getGrammar()->wrap($column);
                    $q->orWhereRaw("{$wrapped} {$likeOp} ?", ["%{$search}%"]);
                }
            }
        });
    }

    /**
     * Apply relation-based search.
     */
    protected function applyRelationSearch(Builder $query, string $column, string $search, string $likeOp = 'LIKE'): void
    {
        $parts = explode('.', $column);
        $relationColumn = array_pop($parts);
        $relation = implode('.', $parts);

        $query->orWhereHas($relation, function (Builder $q) use ($relationColumn, $search, $likeOp) {
            $wrapped = $q->getGrammar()->wrap($relationColumn);
            $q->whereRaw("{$wrapped} {$likeOp} ?", ["%{$search}%"]);
        });
    }

    /**
     * Apply full-text search, separating local columns from relation columns.
     *
     * Local columns use MATCH/tsvector; relation columns fall back to LIKE via whereHas.
     */
    protected function applyFullTextWithRelations(Builder $query, string $search): Builder
    {
        $localColumns = [];
        $relationColumns = [];

        foreach ($this->searchColumns as $column) {
            if (str_contains($column, '.')) {
                $relationColumns[] = $column;
            } else {
                $localColumns[] = $column;
            }
        }

        $likeOp = $this->isPostgres($query) ? 'ILIKE' : 'LIKE';

        return $query->where(function (Builder $q) use ($localColumns, $relationColumns, $search, $likeOp) {
            // Apply full-text search on local columns
            if (! empty($localColumns)) {
                if ($this->isPostgres($q)) {
                    $this->applyPostgresFullTextSearch($q, $search, $localColumns, useOr: true);
                } else {
                    $this->applyFullTextSearch($q, $search, $localColumns, useOr: true);
                }
            }

            // Fall back to LIKE for relation columns
            foreach ($relationColumns as $column) {
                $this->applyRelationSearch($q, $column, $search, $likeOp);
            }
        });
    }

    /**
     * Apply MySQL FULLTEXT search.
     *
     * @param  array<string>  $columns  Local (non-relation) column names
     */
    protected function applyFullTextSearch(Builder $query, string $search, array $columns, bool $useOr = false): Builder
    {
        $grammar = $query->getGrammar();
        $columnsSql = implode(', ', array_map(fn (string $col) => $grammar->wrap($col), $columns));

        $method = $useOr ? 'orWhereRaw' : 'whereRaw';
        $query->{$method}(
            "MATCH ({$columnsSql}) AGAINST (? IN BOOLEAN MODE)",
            [$search.'*']
        );

        return $query;
    }

    /**
     * Apply PostgreSQL full-text search using tsvector/tsquery.
     *
     * @param  array<string>  $columns  Local (non-relation) column names
     */
    protected function applyPostgresFullTextSearch(Builder $query, string $search, array $columns, bool $useOr = false): Builder
    {
        $grammar = $query->getGrammar();
        $colsSql = array_map(
            fn (string $col) => 'coalesce('.$grammar->wrap($col).", '')",
            $columns,
        );

        $tsvector = "to_tsvector('simple', ".implode(" || ' ' || ", $colsSql).')';
        $tsquery = "to_tsquery('simple', ?)";

        // Convert search terms: "hello world" → "hello:* & world:*"
        $terms = preg_split('/\s+/', trim($search));
        $tsValue = implode(' & ', array_map(fn (string $t) => $t.':*', $terms));

        $method = $useOr ? 'orWhereRaw' : 'whereRaw';
        $query->{$method}("{$tsvector} @@ {$tsquery}", [$tsValue]);

        return $query;
    }

    /**
     * Quick method to create a global search filter.
     */
    public static function global(?string $name = 'search'): static
    {
        return static::make($name)
            ->label(__('filament-dcat-filters::filament-dcat-filters.fulltext.global_search'));
    }
}
