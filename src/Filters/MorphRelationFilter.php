<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Closure;
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

class MorphRelationFilter extends Filter
{
    use HasFilterState;
    use HasInlineLabel;
    use HasLabelResolver;
    use HasModelOptions;

    protected string $morphMode = 'morphTo'; // morphTo | morphToMany

    protected array $types = [];

    protected ?Closure $constraint = null;

    protected function describeState(): FilterStateDescriptor
    {
        if ($this->morphMode === 'morphToMany' && $this->multiple) {
            return FilterStateDescriptor::make()
                ->fields(['values'])
                ->type(StateType::Multiple)
                ->emptyWhen(fn (array $data) => empty($data['values'] ?? []))
                ->capabilities(['multiple', 'indicator'])
                ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
        }

        return FilterStateDescriptor::make()
            ->fields(['value'])
            ->type(StateType::Single)
            ->emptyWhen(fn (array $data) => ($data['value'] ?? '') === '')
            ->capabilities(['indicator'])
            ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    public function morphTo(): static
    {
        $this->morphMode = 'morphTo';

        return $this;
    }

    public function morphToMany(): static
    {
        $this->morphMode = 'morphToMany';

        return $this;
    }

    public function types(array $types): static
    {
        $this->types = $types;
        $this->configureForm();

        return $this;
    }

    public function model(string $modelClass): static
    {
        $this->modelClass = $modelClass;
        $this->configureForm();

        return $this;
    }

    public function titleColumn(string $column): static
    {
        $this->titleColumn = $column;
        $this->configureForm();

        return $this;
    }

    public function keyColumn(string $column): static
    {
        $this->keyColumn = $column;
        $this->configureForm();

        return $this;
    }

    public function constrainedBy(?Closure $constraint): static
    {
        $this->constraint = $constraint;

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        $this->configureForm();

        return $this;
    }

    public function getMorphMode(): string
    {
        return $this->morphMode;
    }

    protected function configureForm(): void
    {
        $labelResolver = $this->labelResolver();

        if ($this->morphMode === 'morphTo') {
            if (empty($this->types)) {
                return;
            }

            $options = [];
            foreach ($this->types as $key => $config) {
                if (is_string($config)) {
                    $options[$key] = class_basename($config);
                } elseif (is_array($config) && isset($config['label'])) {
                    $options[$key] = $config['label'];
                } else {
                    $options[$key] = $key;
                }
            }

            $component = Select::make('value')
                ->label($labelResolver)
                ->options($options)
                ->native(false)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.morph_relation.select_type'))
                ->columnSpanFull();

            $this->applyInlineLabel($component, $labelResolver);
            $this->form([$component]);
            $this->configureQuery();

            return;
        }

        // morphToMany
        if (! $this->modelClass) {
            return;
        }

        $modelClass = $this->modelClass;
        $titleColumn = $this->titleColumn;
        $keyColumn = $this->keyColumn;
        $fieldName = $this->multiple ? 'values' : 'value';

        $component = Select::make($fieldName)
            ->label($labelResolver)
            ->options(function () use ($modelClass, $titleColumn, $keyColumn) {
                return $modelClass::query()
                    ->when($this->constraint, fn ($q) => ($this->constraint)($q))
                    ->limit(100)
                    ->pluck($titleColumn, $keyColumn)
                    ->toArray();
            })
            ->multiple($this->multiple)
            ->searchable()
            ->native(false)
            ->preload()
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.morph_relation.select_record'))
            ->columnSpanFull();

        $this->applyInlineLabel($component, $labelResolver);
        $this->form([$component]);
        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $relationship = $this->getName();

            if ($this->morphMode === 'morphTo') {
                $value = $data['value'] ?? null;

                if ($value === null || $value === '') {
                    return $query;
                }

                $typeConfig = $this->types[$value] ?? null;

                if (! $typeConfig) {
                    return $query;
                }

                $modelClass = is_string($typeConfig) ? $typeConfig : ($typeConfig['model'] ?? null);

                if (! $modelClass || ! class_exists($modelClass)) {
                    return $query;
                }

                return $query->whereHasMorph($relationship, [$modelClass]);
            }

            // morphToMany
            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return $query;
                }

                return $query->whereHas(
                    $relationship,
                    fn (Builder $q) => $q->whereIn($this->keyColumn, $values)
                );
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            return $query->whereHas(
                $relationship,
                fn (Builder $q) => $q->where($this->keyColumn, $value)
            );
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->resolveLabel();

            if ($this->morphMode === 'morphTo') {
                $value = $data['value'] ?? null;

                if ($value === null || $value === '') {
                    return [];
                }

                $typeConfig = $this->types[$value] ?? null;
                $displayLabel = $value;

                if (is_array($typeConfig) && isset($typeConfig['label'])) {
                    $displayLabel = $typeConfig['label'];
                } elseif (is_string($typeConfig)) {
                    $displayLabel = class_basename($typeConfig);
                }

                return [
                    Indicator::make("{$label}: {$displayLabel}")
                        ->removeField('value'),
                ];
            }

            // morphToMany
            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return [];
                }

                if ($this->modelClass && class_exists($this->modelClass)) {
                    try {
                        $names = $this->modelClass::query()
                            ->whereIn($this->keyColumn, $values)
                            ->pluck($this->titleColumn)
                            ->implode(', ');

                        return [
                            Indicator::make("{$label}: {$names}")
                                ->removeField('values'),
                        ];
                    } catch (\Exception) {
                        // fallback
                    }
                }

                return [
                    Indicator::make("{$label}: ".implode(', ', $values))
                        ->removeField('values'),
                ];
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            if ($this->modelClass && class_exists($this->modelClass)) {
                try {
                    $record = $this->modelClass::query()
                        ->where($this->keyColumn, $value)
                        ->first();

                    if ($record) {
                        return [
                            Indicator::make("{$label}: {$record->{$this->titleColumn}}")
                                ->removeField('value'),
                        ];
                    }
                } catch (\Exception) {
                    // fallback
                }
            }

            return [
                Indicator::make("{$label}: {$value}")
                    ->removeField('value'),
            ];
        });
    }
}
