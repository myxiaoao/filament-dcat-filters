<?php

namespace Cooper\FilamentDcatFilters\Filters;

use BackedEnum;
use Closure;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class EnumFilter extends Filter
{
    protected ?string $enumClass = null;

    protected array $excluded = [];

    protected string|Closure|null $labelUsing = null;

    protected string|Closure|null $valueUsing = null;

    protected bool $multiple = false;

    protected bool $searchable = false;

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    /**
     * Set the enum class to use.
     */
    public function enum(string $class): static
    {
        $this->enumClass = $class;
        $this->configureForm();

        return $this;
    }

    /**
     * Exclude specific enum cases.
     */
    public function exclude(array $cases): static
    {
        $this->excluded = $cases;
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
     * Enable searchable dropdown.
     */
    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        $this->configureForm();

        return $this;
    }

    /**
     * Set custom label resolver.
     */
    public function labelUsing(string|Closure $method): static
    {
        $this->labelUsing = $method;
        $this->configureForm();

        return $this;
    }

    /**
     * Set custom value resolver.
     */
    public function valueUsing(string|Closure $method): static
    {
        $this->valueUsing = $method;
        $this->configureForm();

        return $this;
    }

    /**
     * Get the options from the enum class.
     */
    protected function getEnumOptions(): array
    {
        if (! $this->enumClass || ! enum_exists($this->enumClass)) {
            return [];
        }

        $options = [];

        foreach ($this->enumClass::cases() as $case) {
            if (in_array($case, $this->excluded, true)) {
                continue;
            }

            $value = $this->resolveEnumValue($case);
            $label = $this->resolveEnumLabel($case);

            $options[$value] = $label;
        }

        return $options;
    }

    /**
     * Resolve the value for an enum case.
     */
    protected function resolveEnumValue(UnitEnum $case): string
    {
        if ($this->valueUsing instanceof Closure) {
            return (string) ($this->valueUsing)($case);
        }

        if (is_string($this->valueUsing) && method_exists($case, $this->valueUsing)) {
            return (string) $case->{$this->valueUsing}();
        }

        if ($case instanceof BackedEnum) {
            return (string) $case->value;
        }

        return $case->name;
    }

    /**
     * Resolve the label for an enum case.
     */
    protected function resolveEnumLabel(UnitEnum $case): string
    {
        if ($this->labelUsing instanceof Closure) {
            return (string) ($this->labelUsing)($case);
        }

        if (is_string($this->labelUsing) && method_exists($case, $this->labelUsing)) {
            return (string) $case->{$this->labelUsing}();
        }

        if (method_exists($case, 'getLabel')) {
            return $case->getLabel();
        }

        if (method_exists($case, 'label')) {
            return $case->label();
        }

        return $case->name;
    }

    /**
     * Configure form component based on settings.
     */
    protected function configureForm(): void
    {
        $options = $this->getEnumOptions();

        if (empty($options)) {
            return;
        }

        $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));

        if ($this->multiple) {
            $this->form([
                Select::make('values')
                    ->label($label)
                    ->options($options)
                    ->searchable($this->searchable)
                    ->multiple()
                    ->native(false)
                    ->placeholder(__('filament-dcat-filters::filament-dcat-filters.enum.placeholder_multiple'))
                    ->columnSpanFull(),
            ]);
        } else {
            $this->form([
                Select::make('value')
                    ->label($label)
                    ->options($options)
                    ->searchable($this->searchable)
                    ->native(false)
                    ->placeholder(__('filament-dcat-filters::filament-dcat-filters.enum.placeholder_single'))
                    ->columnSpanFull(),
            ]);
        }

        $this->configureQuery($options);
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(array $options): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $column = $this->getName();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return $query;
                }

                return $query->whereIn($column, $values);
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            return $query->where($column, $value);
        });

        $this->indicateUsing(function (array $data) use ($options): array {
            $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return [];
                }

                $labels = array_map(fn ($value) => $options[$value] ?? $value, $values);

                return [
                    Indicator::make("{$label}: ".implode(', ', $labels))
                        ->removeField('values'),
                ];
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $valueLabel = $options[$value] ?? $value;

            return [
                Indicator::make("{$label}: {$valueLabel}")
                    ->removeField('value'),
            ];
        });
    }
}
