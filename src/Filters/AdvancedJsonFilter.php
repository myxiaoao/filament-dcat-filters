<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasDatabaseDriver;
use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class AdvancedJsonFilter extends Filter
{
    use HasColumnName;
    use HasDatabaseDriver;
    use HasFilterState;
    use HasInlineLabel;
    use HasLabelResolver;

    protected string $mode = 'arrayContains'; // arrayContains | pathExists | hasKey

    protected ?string $jsonPath = null;

    protected ?string $jsonKey = null;

    protected array $options = [];

    protected bool $multiple = false;

    protected function describeState(): FilterStateDescriptor
    {
        if ($this->mode === 'pathExists' || $this->mode === 'hasKey') {
            return FilterStateDescriptor::make()
                ->fields(['enabled'])
                ->type(StateType::Toggle)
                ->emptyWhen(fn (array $data) => empty($data['enabled']))
                ->capabilities(['indicator'])
                ->databaseSupport(['mysql', 'pgsql'])
                ->limitations(['pathExists and hasKey not supported on SQLite']);
        }

        if ($this->multiple) {
            return FilterStateDescriptor::make()
                ->fields(['values'])
                ->type(StateType::Multiple)
                ->emptyWhen(fn (array $data) => empty($data['values'] ?? []))
                ->capabilities(['multiple', 'indicator'])
                ->databaseSupport(['mysql', 'pgsql'])
                ->degradedSupport(['sqlite']);
        }

        return FilterStateDescriptor::make()
            ->fields(['value'])
            ->type(StateType::Single)
            ->capabilities(['indicator'])
            ->databaseSupport(['mysql', 'pgsql'])
            ->degradedSupport(['sqlite']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    public function arrayContains(): static
    {
        $this->mode = 'arrayContains';
        $this->configureForm();

        return $this;
    }

    public function pathExists(string $path): static
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_.]*$/', $path)) {
            throw new \InvalidArgumentException("Invalid JSON path: {$path}");
        }

        $this->mode = 'pathExists';
        $this->jsonPath = $path;
        $this->configureForm();

        return $this;
    }

    public function hasKey(string $key): static
    {
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $key)) {
            throw new \InvalidArgumentException("Invalid JSON key: {$key}");
        }

        $this->mode = 'hasKey';
        $this->jsonKey = $key;
        $this->configureForm();

        return $this;
    }

    public function options(array $options): static
    {
        $this->options = $options;
        $this->configureForm();

        return $this;
    }

    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        $this->configureForm();

        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    protected function configureForm(): void
    {
        $labelResolver = $this->labelResolver();

        if ($this->mode === 'pathExists' || $this->mode === 'hasKey') {
            $displayLabel = $this->mode === 'pathExists'
                ? __('filament-dcat-filters::filament-dcat-filters.advanced_json.path_exists', ['path' => $this->jsonPath ?? ''])
                : __('filament-dcat-filters::filament-dcat-filters.advanced_json.has_key', ['key' => $this->jsonKey ?? '']);

            $component = Toggle::make('enabled')
                ->label($displayLabel)
                ->columnSpanFull();

            $this->form([$component]);
            $this->configureQuery();

            return;
        }

        // arrayContains mode
        if (! empty($this->options)) {
            $fieldName = $this->multiple ? 'values' : 'value';
            $component = Select::make($fieldName)
                ->label($labelResolver)
                ->options($this->options)
                ->multiple($this->multiple)
                ->searchable()
                ->native(false)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.advanced_json.select_placeholder'))
                ->columnSpanFull();

            $this->applyInlineLabel($component, $labelResolver);
        } else {
            $component = TextInput::make('value')
                ->label($labelResolver)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.advanced_json.text_placeholder'))
                ->columnSpanFull();

            $this->applyInlineLabel($component, $labelResolver);
        }

        $this->form([$component]);
        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $column = $this->resolveColumnName();

            if ($this->mode === 'pathExists') {
                if (empty($data['enabled'])) {
                    return $query;
                }

                $this->assertDriverSupported($query);

                return $this->applyPathExists($query, $column);
            }

            if ($this->mode === 'hasKey') {
                if (empty($data['enabled'])) {
                    return $query;
                }

                $this->assertDriverSupported($query);

                return $this->applyHasKey($query, $column);
            }

            // arrayContains
            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return $query;
                }

                $this->assertDriverSupported($query);

                foreach ($values as $value) {
                    $query = $this->applyArrayContains($query, $column, $value);
                }

                return $query;
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            $this->assertDriverSupported($query);

            return $this->applyArrayContains($query, $column, $value);
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->resolveLabel();

            if ($this->mode === 'pathExists' || $this->mode === 'hasKey') {
                if (empty($data['enabled'])) {
                    return [];
                }

                $target = $this->mode === 'pathExists' ? $this->jsonPath : $this->jsonKey;

                return [
                    Indicator::make("{$label}: {$this->mode}({$target})")
                        ->removeField('enabled'),
                ];
            }

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return [];
                }

                $display = implode(', ', array_map(fn ($v) => $this->options[$v] ?? $v, $values));

                return [
                    Indicator::make("{$label}: {$display}")
                        ->removeField('values'),
                ];
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $display = $this->options[$value] ?? $value;

            return [
                Indicator::make("{$label}: {$display}")
                    ->removeField('value'),
            ];
        });
    }

    protected function applyArrayContains(Builder $query, string $column, mixed $value): Builder
    {
        $wrappedColumn = $query->getGrammar()->wrap($column);

        if ($this->isPostgres($query)) {
            return $query->whereRaw(
                "{$wrappedColumn}::jsonb @> ?::jsonb",
                [json_encode([$value])]
            );
        }

        // MySQL / SQLite (degraded: json_each fallback for SQLite)
        $driver = $this->resolveDriver($query);

        if ($driver === 'sqlite') {
            return $query->whereExists(function ($subQuery) use ($column, $value) {
                $subQuery->selectRaw('1')
                    ->from(\DB::raw("json_each({$column}) AS je"))
                    ->whereRaw('je.value = ?', [$value]);
            });
        }

        // MySQL
        return $query->whereRaw(
            "JSON_CONTAINS({$wrappedColumn}, ?, '$')",
            [json_encode($value)]
        );
    }

    protected function applyPathExists(Builder $query, string $column): Builder
    {
        $wrappedColumn = $query->getGrammar()->wrap($column);
        $dotPath = '$.'.str_replace('->', '.', $this->jsonPath);

        if ($this->isPostgres($query)) {
            // For nested paths, use jsonb_path_exists
            $pgPath = '$.'.str_replace('->', '.', $this->jsonPath);

            return $query->whereRaw(
                "jsonb_path_exists({$wrappedColumn}::jsonb, ?::jsonpath)",
                [$pgPath]
            );
        }

        // MySQL
        return $query->whereRaw(
            "JSON_CONTAINS_PATH({$wrappedColumn}, 'one', ?)",
            [$dotPath]
        );
    }

    protected function applyHasKey(Builder $query, string $column): Builder
    {
        $wrappedColumn = $query->getGrammar()->wrap($column);

        if ($this->isPostgres($query)) {
            return $query->whereRaw(
                "{$wrappedColumn}::jsonb ?? ?",
                [$this->jsonKey]
            );
        }

        // MySQL
        return $query->whereRaw(
            "JSON_CONTAINS_PATH({$wrappedColumn}, 'one', ?)",
            ['$.'.$this->jsonKey]
        );
    }
}
