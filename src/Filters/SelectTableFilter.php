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
     * Configure the form component.
     */
    protected function configureForm(): void
    {
        if (! $this->modelClass && ! $this->relationship) {
            return;
        }

        $label = $this->getLabel() ?? $this->getName();
        $modelClass = $this->modelClass;
        $titleColumn = $this->titleColumn;

        // Use Select component with relationship as a simpler alternative
        $this->form([
            Select::make($this->multiple ? 'values' : 'value')
                ->label($label)
                ->options(function () use ($modelClass, $titleColumn) {
                    if (! $modelClass) {
                        return [];
                    }

                    return $modelClass::query()
                        ->limit(100)
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
            $column = $this->getName();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
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

            if (empty($value)) {
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

            if (! $model) {
                return [];
            }

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return [];
                }

                $records = $model::whereIn('id', $values)->get();
                $names = $records->pluck($this->titleColumn ?? 'name')->implode(', ');

                return [
                    Indicator::make("{$label}: {$names}")
                        ->removeField('values'),
                ];
            }

            $value = $data['value'] ?? null;

            if (empty($value)) {
                return [];
            }

            $record = $model::find($value);
            $name = $record ? $record->{$this->titleColumn ?? 'name'} : $value;

            return [
                Indicator::make("{$label}: {$name}")
                    ->removeField('value'),
            ];
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
