<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Closure;
use Filament\Forms\Components\ViewField;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * ModalSelectFilter - Dcat Admin style modal selection filter
 *
 * Referenced from Dcat Admin's SelectTable implementation, provides modal table selection functionality.
 *
 * @example
 * ModalSelectFilter::make('user_id')
 *     ->label('User')
 *     ->model(User::class, 'name', 'id')
 *     ->dialogTitle('Select User')
 *     ->dialogWidth('900px')
 *     ->multiple()
 */
class ModalSelectFilter extends Filter
{
    protected ?string $modelClass = null;

    protected ?string $relationship = null;

    protected ?string $titleColumn = 'name';

    protected ?string $keyColumn = 'id';

    protected bool $multiple = false;

    protected ?string $dialogTitle = null;

    protected ?string $dialogWidth = '900px';

    protected ?Closure $modifyQueryUsing = null;

    protected array $searchColumns = [];

    protected array $displayColumns = [];

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);

        $this->schema(fn (): array => $this->getFilterSchema());

        $this->configureQuery();
    }

    /**
     * Set the model class.
     *
     * @param  class-string<Model>  $modelClass  Model class name
     * @param  string  $titleColumn  Display column name (e.g., 'name')
     * @param  string  $keyColumn  Key column name (default 'id')
     */
    public function model(string $modelClass, string $titleColumn = 'name', string $keyColumn = 'id'): static
    {
        $this->modelClass = $modelClass;
        $this->titleColumn = $titleColumn;
        $this->keyColumn = $keyColumn;

        return $this;
    }

    /**
     * Set the relationship.
     */
    public function relationship(string $relationship, string $titleColumn = 'name', string $keyColumn = 'id'): static
    {
        $this->relationship = $relationship;
        $this->titleColumn = $titleColumn;
        $this->keyColumn = $keyColumn;

        return $this;
    }

    /**
     * Set the dialog title.
     */
    public function dialogTitle(string $title): static
    {
        $this->dialogTitle = $title;

        return $this;
    }

    /**
     * Set the dialog title (alias).
     */
    public function modalTitle(string $title): static
    {
        return $this->dialogTitle($title);
    }

    /**
     * Set the dialog width.
     *
     * @param  string  $width  e.g., '900px', '80%'
     */
    public function dialogWidth(string $width): static
    {
        $this->dialogWidth = $width;

        return $this;
    }

    /**
     * Set the dialog width (alias).
     *
     * @param  string  $width  e.g., '900px', '80%', '5xl' etc. Tailwind size class names
     */
    public function modalWidth(string $width): static
    {
        return $this->dialogWidth($width);
    }

    /**
     * Enable multiple selection.
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;

        return $this;
    }

    /**
     * Set searchable columns.
     */
    public function searchable(array $columns): static
    {
        $this->searchColumns = $columns;

        return $this;
    }

    /**
     * Set table display columns.
     *
     * @param  array  $columns  Columns array, e.g., ['id' => 'ID', 'name' => 'Name', 'email' => 'Email']
     */
    public function displayColumns(array $columns): static
    {
        $this->displayColumns = $columns;

        return $this;
    }

    /**
     * Customize table query.
     */
    public function modifyQueryUsing(?Closure $callback): static
    {
        $this->modifyQueryUsing = $callback;

        return $this;
    }

    /**
     * Build the filter schema components.
     *
     * @return array<ViewField>
     */
    protected function getFilterSchema(): array
    {
        $filterKey = $this->getName();

        $field = ViewField::make('value')
            ->label($this->getLabel() ?? $filterKey)
            ->view('filament-dcat-filters::filters.modal-select')
            ->viewData([
                'filterName' => $filterKey,
                'modelClass' => $this->modelClass,
                'titleColumn' => $this->titleColumn,
                'keyColumn' => $this->keyColumn,
                'multiple' => $this->multiple,
                'dialogTitle' => $this->getDialogTitle(),
                'dialogWidth' => $this->dialogWidth,
                'searchColumns' => $this->searchColumns,
                'displayColumns' => $this->displayColumns,
            ])
            ->columnSpanFull();

        return [$field];
    }

    /**
     * Check if a filter value is considered empty.
     */
    protected function isValueEmpty(mixed $value): bool
    {
        return $value === null || $value === '' || $value === [];
    }

    /**
     * Configure query logic.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            if ($this->isValueEmpty($value)) {
                return $query;
            }

            $column = $this->getName();

            // Handle relationship filtering
            if ($this->relationship) {
                if ($this->multiple) {
                    $values = is_array($value) ? $value : explode(',', (string) $value);

                    return $query->whereHas(
                        $this->relationship,
                        fn (Builder $query) => $query->whereIn($this->keyColumn, $values)
                    );
                }

                return $query->whereHas(
                    $this->relationship,
                    fn (Builder $query) => $query->where($this->keyColumn, $value)
                );
            }

            // Handle direct column filtering
            if ($this->multiple) {
                $values = is_array($value) ? $value : explode(',', (string) $value);

                return $query->whereIn($column, $values);
            }

            return $query->where($column, $value);
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if ($this->isValueEmpty($value)) {
                return [];
            }

            $label = $this->getLabel() ?? $this->getName();
            $model = $this->modelClass;

            if (! $model || ! class_exists($model) || ! is_subclass_of($model, Model::class)) {
                return [];
            }

            try {
                if ($this->multiple) {
                    $values = is_array($value) ? $value : explode(',', (string) $value);
                    $names = $model::query()
                        ->whereIn($this->keyColumn, $values)
                        ->pluck($this->titleColumn)
                        ->implode(', ');

                    return [
                        Indicator::make("{$label}: {$names}")
                            ->removeField('value')
                            ->removeField('modal_select'),
                    ];
                }

                $record = $model::query()->where($this->keyColumn, $value)->first();
                $name = $record ? $record->{$this->titleColumn} : $value;

                return [
                    Indicator::make("{$label}: {$name}")
                        ->removeField('value')
                        ->removeField('modal_select'),
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
    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    /**
     * Get the title column.
     */
    public function getTitleColumn(): string
    {
        return $this->titleColumn;
    }

    /**
     * Get the key column.
     */
    public function getKeyColumn(): string
    {
        return $this->keyColumn;
    }

    /**
     * Check if multiple selection is enabled.
     */
    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * Get the dialog title.
     */
    public function getDialogTitle(): string
    {
        return $this->dialogTitle ?? ($this->getLabel() ?? __('filament-dcat-filters::filament-dcat-filters.modal_select.default_title'));
    }

    /**
     * Get the dialog width.
     */
    public function getDialogWidth(): string
    {
        return $this->dialogWidth;
    }
}
