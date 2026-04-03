<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Closure;
use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
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
    use HasColumnName;
    use HasInlineLabel;

    protected ?string $modelClass = null;

    protected ?string $relationship = null;

    /** @var string|null Column name on the model used as display label (distinct from HasRelationship::$relationshipTitleColumn) */
    protected ?string $titleColumn = 'name';

    protected ?string $keyColumn = 'id';

    protected bool $multiple = false;

    protected ?string $dialogTitle = null;

    protected ?string $dialogWidth = '5xl';

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

        $this->configureForm();
        $this->configureQuery();
    }

    /**
     * Configure the form component.
     */
    protected function configureForm(): void
    {
        $filterKey = $this->getName();
        $shouldInline = $this->shouldInlineLabel();

        $viewField = ViewField::make('value')
            ->label(fn () => $this->evaluate($this->getLabel()) ?? $filterKey)
            ->view('filament-dcat-filters::filters.modal-select')
            ->viewData(fn () => [
                'filterName' => $filterKey,
                'label' => $this->evaluate($this->getLabel()) ?? $filterKey,
                'modelClass' => $this->modelClass,
                'titleColumn' => $this->titleColumn,
                'keyColumn' => $this->keyColumn,
                'multiple' => $this->multiple,
                'dialogTitle' => $this->getDialogTitle(),
                'dialogWidth' => $this->dialogWidth,
                'searchColumns' => $this->searchColumns,
                'displayColumns' => $this->displayColumns,
                'inlineLabel' => $shouldInline,
            ])
            ->columnSpanFull();

        if ($shouldInline) {
            $viewField->hiddenLabel();
        }

        $this->form([$viewField]);
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
     * Set the relationship and the related model class.
     *
     * The model class is required for the modal table UI and label fetching.
     * If only the relationship name is provided without a model class already set,
     * the modal table will not render — you must call model() first or pass a modelClass.
     *
     * @param  class-string<Model>|null  $modelClass  Related model class (recommended)
     *
     * @example
     * ModalSelectFilter::make('user_id')
     *     ->relationship('user', 'name', 'id', User::class)
     *
     * // Or equivalently:
     * ModalSelectFilter::make('user_id')
     *     ->model(User::class, 'name', 'id')
     *     ->relationship('user')
     */
    public function relationship(string $relationship, string $titleColumn = 'name', string $keyColumn = 'id', ?string $modelClass = null): static
    {
        $this->relationship = $relationship;
        $this->titleColumn = $titleColumn;
        $this->keyColumn = $keyColumn;

        if ($modelClass !== null) {
            $this->modelClass = $modelClass;
        }

        // Reconfigure form and query so the modal table renders with the model
        $this->configureForm();
        $this->configureQuery();

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
     * Configure query logic.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            if ($this->isValueEmpty($value)) {
                return $query;
            }

            // Use custom column name if set, otherwise default to filter name
            $column = $this->resolveColumnName();

            // Apply custom query modifier if set
            if ($this->modifyQueryUsing) {
                return ($this->modifyQueryUsing)($query, $value, $column);
            }

            // Handle relationship filtering
            if ($this->relationship) {
                if ($this->multiple) {
                    $values = is_array($value) ? $value : array_filter(array_map('trim', explode(',', (string) $value)), fn ($v) => $v !== '');

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
                $values = is_array($value) ? $value : array_filter(array_map('trim', explode(',', (string) $value)), fn ($v) => $v !== '');

                return $query->whereIn($column, $values);
            }

            return $query->where($column, $value);
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if ($this->isValueEmpty($value)) {
                return [];
            }

            $label = $this->evaluate($this->getLabel()) ?? $this->getName();
            $model = $this->modelClass;

            // If no model class, still show the raw value as indicator
            if (! $model || ! class_exists($model) || ! is_subclass_of($model, Model::class)) {
                $displayValue = is_array($value) ? implode(', ', $value) : (string) $value;

                return [
                    Indicator::make("{$label}: {$displayValue}")
                        ->removeField('value')
                        ->removeField('modal_select'),
                ];
            }

            try {
                if ($this->multiple) {
                    $values = is_array($value) ? $value : array_filter(array_map('trim', explode(',', (string) $value)), fn ($v) => $v !== '');
                    $names = $model::query()
                        ->select([$this->keyColumn, $this->titleColumn])
                        ->whereIn($this->keyColumn, $values)
                        ->pluck($this->titleColumn)
                        ->implode(', ');

                    return [
                        Indicator::make("{$label}: {$names}")
                            ->removeField('value')
                            ->removeField('modal_select'),
                    ];
                }

                $record = $model::query()->select([$this->keyColumn, $this->titleColumn])->where($this->keyColumn, $value)->first();
                $name = $record ? $record->{$this->titleColumn} : $value;

                return [
                    Indicator::make("{$label}: {$name}")
                        ->removeField('value')
                        ->removeField('modal_select'),
                ];
            } catch (\Exception $e) {
                report($e);

                return [
                    Indicator::make($label.': '.__('filament-dcat-filters::filament-dcat-filters.accessibility.error_occurred'))
                        ->removeField('value')
                        ->removeField('modal_select'),
                ];
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
        if ($this->dialogTitle) {
            return $this->dialogTitle;
        }

        $label = $this->evaluate($this->getLabel());

        return $label ?? __('filament-dcat-filters::filament-dcat-filters.modal_select.default_title');
    }

    /**
     * Get the dialog width.
     */
    public function getDialogWidth(): string
    {
        return $this->dialogWidth;
    }
}
