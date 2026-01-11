<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class FullTextFilter extends Filter
{
    protected array $searchColumns = [];

    protected int $minLength = 2;

    protected int $debounce = 300;

    protected bool $useFullText = false;

    protected ?string $placeholder = null;

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
        $label = $this->getLabel() ?? __('filament-dcat-filters::filament-dcat-filters.fulltext.label');
        $placeholder = $this->placeholder ?? __('filament-dcat-filters::filament-dcat-filters.fulltext.placeholder');

        $this->form([
            TextInput::make('search')
                ->label($label)
                ->placeholder($placeholder)
                ->minLength($this->minLength)
                ->debounce($this->debounce)
                ->prefixIcon('heroicon-o-magnifying-glass')
                ->columnSpanFull(),
        ]);

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

            if ($this->useFullText) {
                return $this->applyFullTextSearch($query, $search);
            }

            return $this->applyLikeSearch($query, $search);
        });

        $this->indicateUsing(function (array $data): array {
            $search = trim($data['search'] ?? '');

            if (strlen($search) < $this->minLength) {
                return [];
            }

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
        return $query->where(function (Builder $q) use ($search) {
            foreach ($this->searchColumns as $column) {
                if (str_contains($column, '.')) {
                    $this->applyRelationSearch($q, $column, $search);
                } else {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            }
        });
    }

    /**
     * Apply relation-based search.
     */
    protected function applyRelationSearch(Builder $query, string $column, string $search): void
    {
        $parts = explode('.', $column);
        $relationColumn = array_pop($parts);
        $relation = implode('.', $parts);

        $query->orWhereHas($relation, function (Builder $q) use ($relationColumn, $search) {
            $q->where($relationColumn, 'LIKE', "%{$search}%");
        });
    }

    /**
     * Apply MySQL FULLTEXT search.
     */
    protected function applyFullTextSearch(Builder $query, string $search): Builder
    {
        $columns = implode(', ', $this->searchColumns);

        return $query->whereRaw(
            "MATCH ({$columns}) AGAINST (? IN BOOLEAN MODE)",
            [$search.'*']
        );
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
