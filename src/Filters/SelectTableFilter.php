<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Closure;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\Column;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class SelectTableFilter extends Filter
{
    protected ?string $modelClass = null;

    protected ?string $relationship = null;

    protected array $tableColumns = [];

    protected array $searchColumns = [];

    protected bool $multiple = false;

    protected ?string $modalWidth = null;

    protected ?string $titleColumn = 'name';

    protected ?Closure $modifyQueryUsing = null;

    protected ?int $optionsLimit = null;

    protected ?string $columnName = null;

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
     */
    public function relationship(string $relationship, ?string $titleColumn = 'name'): static
    {
        $this->relationship = $relationship;
        $this->titleColumn = $titleColumn;

        // Try to get model class from relationship
        // Note: This is a simplified version, might need adjustment based on actual usage
        $this->configureForm();

        return $this;
    }

    /**
     * Set the columns to display in the table.
     *
     * @param  array<Column>  $columns
     */
    public function tableColumns(array $columns): static
    {
        $this->tableColumns = $columns;

        return $this;
    }

    /**
     * Set the columns that can be searched.
     */
    public function searchable(array|bool $columns = true): static
    {
        if (is_bool($columns)) {
            $this->searchColumns = $columns ? ['name'] : [];
        } else {
            $this->searchColumns = $columns;
        }

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
     * Set the modal width.
     */
    public function modalWidth(string $width): static
    {
        $this->modalWidth = $width;

        return $this;
    }

    /**
     * Modify the query used to fetch records.
     */
    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

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
     * Set the column name for the comparison.
     * This allows the filter name to differ from the actual database column.
     */
    public function column(string $column): static
    {
        $this->columnName = $column;
        $this->configureForm();

        return $this;
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

        $labelResolver = fn (): string => $this->getLabel() ?? $this->getName();
        $modelClass = $this->modelClass;
        $titleColumn = $this->titleColumn;

        // Use Select component with relationship as a simpler alternative
        $limit = $this->getOptionsLimit();

        $this->form([
            Select::make($this->multiple ? 'values' : 'value')
                ->label($labelResolver)
                ->options(function () use ($modelClass, $titleColumn, $limit) {
                    if (! $modelClass) {
                        return [];
                    }

                    return $modelClass::query()
                        ->limit($limit)
                        ->pluck($titleColumn ?? 'name', 'id')
                        ->toArray();
                })
                ->searchable()
                ->multiple($this->multiple)
                ->native(false)
                ->preload()
                ->placeholder($this->multiple ? __('filament-dcat-filters::filament-dcat-filters.select_table.placeholder_multiple') : __('filament-dcat-filters::filament-dcat-filters.select_table.placeholder_single'))
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
            // Use custom column name if set, otherwise default to filter name
            $column = $this->columnName ?? $this->getName();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (! is_array($values) || count($values) === 0) {
                    return $query;
                }

                // Handle relationship filtering
                if ($this->relationship) {
                    return $query->whereHas(
                        $this->relationship,
                        fn (Builder $query) => $query->whereIn('id', $values)
                    );
                }

                // Handle direct column filtering
                return $query->whereIn($column, $values);
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            // Handle relationship filtering
            if ($this->relationship) {
                return $query->whereHas(
                    $this->relationship,
                    fn (Builder $query) => $query->where('id', $value)
                );
            }

            // Handle direct column filtering
            return $query->where($column, $value);
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->getLabel() ?? $this->getName();
            $model = $this->getModel();

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
                        ->whereIn('id', $values)
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

                $record = $model::query()->find($value);
                $name = $record ? $record->{$this->titleColumn ?? 'name'} : $value;

                return [
                    Indicator::make("{$label}: {$name}")
                        ->removeField('value'),
                ];
            } catch (\Exception $e) {
                report($e);

                return [];
            }
        });
    }

    /**
     * Get the model class.
     */
    protected function getModel(): ?string
    {
        return $this->modelClass;
    }
}
