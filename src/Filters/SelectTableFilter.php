<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Closure;
use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Cooper\FilamentDcatFilters\Concerns\HasModelOptions;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SelectTableFilter extends Filter
{
    use HasColumnName;
    use HasFilterState;
    use HasInlineLabel;
    use HasLabelResolver;
    use HasModelOptions;

    protected ?string $relationship = null;

    protected ?Closure $customQueryModifier = null;

    protected ?int $optionsLimit = null;

    protected bool $remoteSearchEnabled = false;

    protected int $searchDebounce = 300;

    protected int $minSearchLength = 1;

    protected array $searchColumns = [];

    protected function describeState(): FilterStateDescriptor
    {
        return FilterStateDescriptor::make()
            ->fields($this->multiple ? ['values'] : ['value'])
            ->type($this->multiple ? StateType::Multiple : StateType::Single)
            ->emptyWhen(fn (array $data) => $this->multiple
                ? (! is_array($data['values'] ?? null) || count($data['values'] ?? []) === 0)
                : ($data['value'] ?? '') === '')
            ->capabilities(array_filter(['relationship', 'indicator', $this->multiple ? 'multiple' : null]))
            ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
    }

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    /**
     * Set the model class for the filter.
     */
    public function model(string $modelClass): static
    {
        $this->modelClass = $modelClass;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the relationship for the filter.
     *
     * When using relationship mode, the model class must be set (via model() or the $modelClass parameter)
     * for the dropdown options to be populated.
     *
     * @param  class-string<Model>|null  $modelClass  Related model class (recommended)
     *
     * @example
     * SelectTableFilter::make('user_id')
     *     ->model(User::class)
     *     ->relationship('user', 'name')
     *
     * // Or in a single call:
     * SelectTableFilter::make('user_id')
     *     ->relationship('user', 'name', User::class)
     */
    public function relationship(string $relationship, ?string $titleColumn = 'name', ?string $modelClass = null): static
    {
        $this->relationship = $relationship;
        $this->titleColumn = $titleColumn;

        if ($modelClass !== null) {
            $this->modelClass = $modelClass;
        }

        // Must call configureForm so dropdown options are populated
        $this->configureForm();

        return $this;
    }

    /**
     * Enable multiple selection.
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        $this->configureForm();

        return $this;
    }

    /**
     * Set a custom query modifier for this filter.
     *
     * This stores the callback separately from Filament's internal query registration.
     * The custom modifier is checked first in configureQuery() — if set, it receives
     * the full query and data array for complete control.
     */
    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->customQueryModifier = $callback;

        return $this;
    }

    /**
     * Set the maximum number of options to load.
     */
    public function optionsLimit(int $limit): static
    {
        $this->optionsLimit = $limit;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the column name and reconfigure the form.
     */
    public function column(string $column): static
    {
        $this->columnName = $column;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the key column for the filter.
     */
    public function keyColumn(string $column): static
    {
        $this->keyColumn = $column;
        $this->configureForm();

        return $this;
    }

    /**
     * Enable server-side search instead of preloading all options.
     * Recommended for large datasets (10k+ rows).
     */
    public function remoteSearch(bool $enabled = true): static
    {
        $this->remoteSearchEnabled = $enabled;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the debounce delay for search input (in milliseconds).
     */
    public function searchDebounce(int $ms): static
    {
        $this->searchDebounce = $ms;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the minimum number of characters before search fires.
     */
    public function minSearchLength(int $length): static
    {
        $this->minSearchLength = $length;

        return $this;
    }

    /**
     * Set the columns to search against for remote search.
     * Defaults to [$titleColumn] if not set.
     */
    public function searchColumns(array $columns): static
    {
        $this->searchColumns = $columns;

        return $this;
    }

    public function isRemoteSearchEnabled(): bool
    {
        return $this->remoteSearchEnabled;
    }

    public function getSearchDebounce(): int
    {
        return $this->searchDebounce;
    }

    public function getMinSearchLength(): int
    {
        return $this->minSearchLength;
    }

    public function getSearchColumns(): array
    {
        return $this->searchColumns;
    }

    /**
     * Get the options limit from config or property.
     */
    protected function getOptionsLimit(): int
    {
        return $this->optionsLimit ?? config('filament-dcat-filters.select_table.options_limit', 100);
    }

    /**
     * Configure the form component.
     */
    protected function configureForm(): void
    {
        if (! $this->modelClass && ! $this->relationship) {
            return;
        }

        $labelResolver = $this->labelResolver();
        $modelClass = $this->modelClass;
        $titleColumn = $this->titleColumn;
        $limit = $this->getOptionsLimit();
        $placeholder = $this->multiple
            ? __('filament-dcat-filters::filament-dcat-filters.select_table.placeholder_multiple')
            : __('filament-dcat-filters::filament-dcat-filters.select_table.placeholder_single');

        $select = Select::make($this->multiple ? 'values' : 'value')
            ->label($labelResolver)
            ->multiple($this->multiple)
            ->native(false)
            ->placeholder($placeholder)
            ->columnSpanFull();

        if ($this->remoteSearchEnabled && $modelClass) {
            $searchCols = ! empty($this->searchColumns) ? $this->searchColumns : [$titleColumn ?? 'name'];
            $keyCol = $this->keyColumn;
            $minLen = $this->minSearchLength;

            $select
                ->getSearchResultsUsing(function (string $search) use ($modelClass, $titleColumn, $keyCol, $searchCols, $limit, $minLen): array {
                    if (strlen($search) < $minLen) {
                        return [];
                    }

                    return $modelClass::query()
                        ->where(function (Builder $q) use ($search, $searchCols) {
                            foreach ($searchCols as $col) {
                                $q->orWhere($col, 'like', "%{$search}%");
                            }
                        })
                        ->limit($limit)
                        ->pluck($titleColumn ?? 'name', $keyCol)
                        ->toArray();
                })
                ->getOptionLabelUsing(function ($value) use ($modelClass, $titleColumn, $keyCol): ?string {
                    if ($value === null || $value === '') {
                        return null;
                    }

                    return $modelClass::query()
                        ->where($keyCol, $value)
                        ->value($titleColumn ?? 'name');
                })
                ->searchable()
                ->searchDebounce($this->searchDebounce);
        } else {
            $select
                ->options(function () use ($modelClass, $titleColumn, $limit) {
                    if (! $modelClass) {
                        return [];
                    }

                    return $modelClass::query()
                        ->limit($limit)
                        ->pluck($titleColumn ?? 'name', $this->keyColumn)
                        ->toArray();
                })
                ->searchable()
                ->preload();
        }

        $this->applyInlineLabel($select, $labelResolver);

        $this->form([$select]);

        $this->configureQuery();
    }

    /**
     * Configure the query logic for this filter.
     *
     * Uses parent::modifyQueryUsing() directly to avoid the overridden
     * modifyQueryUsing() which stores to $customQueryModifier instead of
     * Filament's internal $modifyQueryUsing property.
     */
    protected function configureQuery(): void
    {
        parent::modifyQueryUsing(function (Builder $query, array $data): Builder {
            $column = $this->resolveColumnName();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (! is_array($values) || count($values) === 0) {
                    return $query;
                }

                // Apply custom query modifier if set
                if ($this->customQueryModifier) {
                    return ($this->customQueryModifier)($query, $data);
                }

                // Handle relationship filtering
                if ($this->relationship) {
                    return $query->whereHas(
                        $this->relationship,
                        fn (Builder $query) => $query->whereIn($this->keyColumn, $values)
                    );
                }

                // Handle direct column filtering
                return $query->whereIn($column, $values);
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            // Apply custom query modifier if set
            if ($this->customQueryModifier) {
                return ($this->customQueryModifier)($query, $data);
            }

            // Handle relationship filtering
            if ($this->relationship) {
                return $query->whereHas(
                    $this->relationship,
                    fn (Builder $query) => $query->where($this->keyColumn, $value)
                );
            }

            // Handle direct column filtering
            return $query->where($column, $value);
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->resolveLabel();
            $model = $this->modelClass;

            if (! $model || ! class_exists($model) || ! is_subclass_of($model, Model::class)) {
                return [];
            }

            try {
                if ($this->multiple) {
                    $values = $data['values'] ?? [];

                    if (! is_array($values) || count($values) === 0) {
                        return [];
                    }

                    $names = $model::query()
                        ->whereIn($this->keyColumn, $values)
                        ->pluck($this->titleColumn ?? 'name')
                        ->implode(', ');

                    return [
                        Indicator::make("{$label}: {$names}")
                            ->removeField('values'),
                    ];
                }

                $value = $data['value'] ?? null;

                if ($value === null || $value === '') {
                    return [];
                }

                $record = $model::query()->where($this->keyColumn, $value)->first();
                $name = $record ? $record->{$this->titleColumn ?? 'name'} : $value;

                return [
                    Indicator::make("{$label}: {$name}")
                        ->removeField('value'),
                ];
            } catch (\Exception $e) {
                report($e);

                return [
                    Indicator::make($label.': '.__('filament-dcat-filters::filament-dcat-filters.accessibility.error_occurred'))
                        ->removeField($this->multiple ? 'values' : 'value'),
                ];
            }
        });
    }
}
