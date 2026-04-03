<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Filament\Forms\Components\Select;
use Filament\Forms\Get;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Cascading Select Filter
 *
 * A filter that supports cascading/dependent select dropdowns.
 * When a parent field changes, child field options are updated accordingly.
 */
class CascadingSelectFilter extends Filter
{
    use HasInlineLabel;

    protected array $levels = [];

    protected array $levelConfigs = [];

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    /**
     * Add a cascade level.
     *
     * @param  string  $name  Field/column name
     * @param  string  $label  Display label
     * @param  string  $model  Model class name
     * @param  string|null  $parentField  Parent field name (null for root level)
     * @param  string  $foreignKey  Foreign key column in this model
     * @param  string  $titleColumn  Column to display as option label
     * @param  string  $keyColumn  Column to use as option value
     */
    public function addLevel(
        string $name,
        string $label,
        string $model,
        ?string $parentField = null,
        string $foreignKey = 'parent_id',
        string $titleColumn = 'name',
        string $keyColumn = 'id'
    ): static {
        if (! class_exists($model) || ! is_subclass_of($model, Model::class)) {
            throw new \InvalidArgumentException("Invalid model class: {$model}");
        }

        // Validate column names
        foreach (['foreignKey' => $foreignKey, 'titleColumn' => $titleColumn, 'keyColumn' => $keyColumn] as $param => $col) {
            if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_.]*$/', $col)) {
                throw new \InvalidArgumentException("Invalid {$param}: {$col}");
            }
        }

        $this->levels[] = $name;
        $this->levelConfigs[$name] = [
            'name' => $name,
            'label' => $label,
            'model' => $model,
            'parentField' => $parentField,
            'foreignKey' => $foreignKey,
            'titleColumn' => $titleColumn,
            'keyColumn' => $keyColumn,
        ];

        $this->rebuildForm();

        return $this;
    }

    /**
     * Build a cascading filter from an array of level definitions.
     *
     * @param  array  $levels  Array of level configurations
     *
     * Example:
     * [
     *     ['name' => 'country_id', 'label' => 'Country', 'model' => Country::class],
     *     ['name' => 'state_id', 'label' => 'State', 'model' => State::class, 'parentField' => 'country_id', 'foreignKey' => 'country_id'],
     *     ['name' => 'city_id', 'label' => 'City', 'model' => City::class, 'parentField' => 'state_id', 'foreignKey' => 'state_id'],
     * ]
     */
    public function levels(array $levels): static
    {
        foreach ($levels as $level) {
            $this->addLevel(
                name: $level['name'],
                label: $level['label'],
                model: $level['model'],
                parentField: $level['parentField'] ?? null,
                foreignKey: $level['foreignKey'] ?? 'parent_id',
                titleColumn: $level['titleColumn'] ?? 'name',
                keyColumn: $level['keyColumn'] ?? 'id'
            );
        }

        return $this;
    }

    /**
     * Rebuild the form with current level configurations.
     */
    protected function rebuildForm(): void
    {
        $fields = [];
        $previousField = null;

        foreach ($this->levels as $index => $levelName) {
            $config = $this->levelConfigs[$levelName];
            $field = $this->buildSelectField($config, $previousField, $index);
            $fields[] = $field;
            $previousField = $levelName;
        }

        $this->form($fields);
        $this->configureQuery();
        $this->configureIndicator();
    }

    /**
     * Build a select field for a level.
     */
    protected function buildSelectField(array $config, ?string $previousField, int $index): Select
    {
        $field = Select::make($config['name'])
            ->label($config['label'])
            ->options(function (Get $get) use ($config, $previousField) {
                return $this->getOptionsForLevel($config, $get, $previousField);
            })
            ->native(false)
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.cascading.select_placeholder'))
            ->live();

        // Clear dependent fields when this field changes
        $dependentFields = array_slice($this->levels, $index + 1);

        if (! empty($dependentFields)) {
            $field->afterStateUpdated(function (callable $set) use ($dependentFields) {
                foreach ($dependentFields as $fieldName) {
                    $set($fieldName, null);
                }
            });
        }

        $this->applyInlineLabel($field, $config['label']);

        return $field;
    }

    /**
     * Get options for a level based on parent selection.
     */
    protected function getOptionsForLevel(array $config, Get $get, ?string $previousField): array
    {
        $model = $config['model'];
        $limit = config('filament-dcat-filters.select_table.options_limit', 100);

        // Root level - return all options with limit
        if ($previousField === null) {
            return $model::query()
                ->limit($limit)
                ->pluck($config['titleColumn'], $config['keyColumn'])
                ->toArray();
        }

        // Child level - filter by parent value
        $parentValue = $get($previousField);

        if (empty($parentValue)) {
            return [];
        }

        return $model::query()
            ->where($config['foreignKey'], $parentValue)
            ->limit($limit)
            ->pluck($config['titleColumn'], $config['keyColumn'])
            ->toArray();
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            foreach ($this->levels as $levelName) {
                $value = $data[$levelName] ?? null;

                if ($value !== null && $value !== '') {
                    $query->where($levelName, $value);
                }
            }

            return $query;
        });
    }

    /**
     * Configure the indicator for this filter.
     */
    protected function configureIndicator(): void
    {
        $this->indicateUsing(function (array $data): array {
            $indicators = [];

            // Group values by model class for batch querying
            $modelGroups = [];
            foreach ($this->levels as $levelName) {
                $value = $data[$levelName] ?? null;
                if ($value === null || $value === '') {
                    continue;
                }
                $config = $this->levelConfigs[$levelName];
                $model = $config['model'];
                $modelGroups[$model][] = [
                    'levelName' => $levelName,
                    'value' => $value,
                    'config' => $config,
                ];
            }

            // Batch query per model, grouped by column configuration
            $resolvedLabels = [];
            foreach ($modelGroups as $model => $items) {
                // Group items by keyColumn+titleColumn to handle different column configs for same model
                $columnGroups = [];
                foreach ($items as $item) {
                    $groupKey = $item['config']['keyColumn'].'|'.$item['config']['titleColumn'];
                    $columnGroups[$groupKey][] = $item;
                }

                foreach ($columnGroups as $groupItems) {
                    $ids = array_column($groupItems, 'value');
                    $keyColumn = $groupItems[0]['config']['keyColumn'];
                    $titleColumn = $groupItems[0]['config']['titleColumn'];

                    try {
                        $records = $model::query()
                            ->whereIn($keyColumn, $ids)
                            ->pluck($titleColumn, $keyColumn)
                            ->toArray();
                    } catch (\Exception $e) {
                        $records = [];
                    }

                    foreach ($groupItems as $item) {
                        $resolvedLabels[$item['levelName']] = $records[$item['value']] ?? $item['value'];
                    }
                }
            }

            // Build indicators in level order
            foreach ($this->levels as $levelName) {
                if (! isset($resolvedLabels[$levelName])) {
                    continue;
                }

                $config = $this->levelConfigs[$levelName];
                $indicators[] = Indicator::make("{$config['label']}: {$resolvedLabels[$levelName]}")
                    ->removeField($levelName);
            }

            return $indicators;
        });
    }

    /**
     * Create a location-based cascading filter (Country -> State -> City).
     */
    public static function forLocation(
        string $countryModel,
        string $stateModel,
        string $cityModel,
        ?string $countryLabel = null,
        ?string $stateLabel = null,
        ?string $cityLabel = null
    ): static {
        return static::make('location')
            ->levels([
                [
                    'name' => 'country_id',
                    'label' => $countryLabel ?? __('filament-dcat-filters::filament-dcat-filters.cascading.country'),
                    'model' => $countryModel,
                ],
                [
                    'name' => 'state_id',
                    'label' => $stateLabel ?? __('filament-dcat-filters::filament-dcat-filters.cascading.state'),
                    'model' => $stateModel,
                    'parentField' => 'country_id',
                    'foreignKey' => 'country_id',
                ],
                [
                    'name' => 'city_id',
                    'label' => $cityLabel ?? __('filament-dcat-filters::filament-dcat-filters.cascading.city'),
                    'model' => $cityModel,
                    'parentField' => 'state_id',
                    'foreignKey' => 'state_id',
                ],
            ]);
    }

    /**
     * Create a category-based cascading filter (Parent -> Child -> Grandchild).
     */
    public static function forCategory(
        string $model,
        int $depth = 2,
        ?string $rootLabel = null,
        ?string $childLabel = null,
        string $parentColumn = 'parent_id'
    ): static {
        $filter = static::make('category');

        $levels = [];
        for ($i = 0; $i < $depth; $i++) {
            $isRoot = $i === 0;
            $levels[] = [
                'name' => $isRoot ? 'category_id' : "subcategory_{$i}_id",
                'label' => $isRoot
                    ? ($rootLabel ?? __('filament-dcat-filters::filament-dcat-filters.cascading.category'))
                    : ($childLabel ?? __('filament-dcat-filters::filament-dcat-filters.cascading.subcategory')).' '.($i),
                'model' => $model,
                'parentField' => $isRoot ? null : ($i === 1 ? 'category_id' : 'subcategory_'.($i - 1).'_id'),
                'foreignKey' => $parentColumn,
            ];
        }

        return $filter->levels($levels);
    }
}
