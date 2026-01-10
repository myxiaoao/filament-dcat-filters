<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class BooleanFilter extends Filter
{
    protected string $trueLabel = 'Yes';

    protected string $falseLabel = 'No';

    protected string $allLabel = 'All';

    protected string $displayStyle = 'select';

    protected array|int|null $radioColumns = 3;

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->trueLabel = config('filament-dcat-filters.boolean.default_true_label', 'Yes');
        $this->falseLabel = config('filament-dcat-filters.boolean.default_false_label', 'No');
        $this->allLabel = config('filament-dcat-filters.boolean.default_all_label', 'All');
        $this->displayStyle = config('filament-dcat-filters.boolean.default_display_style', 'select');

        $this->columnSpan(1);
        $this->configureForm();
    }

    /**
     * Set the label for true value.
     */
    public function trueLabel(string $label): static
    {
        $this->trueLabel = $label;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the label for false value.
     */
    public function falseLabel(string $label): static
    {
        $this->falseLabel = $label;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the label for all/null value.
     */
    public function allLabel(string $label): static
    {
        $this->allLabel = $label;
        $this->configureForm();

        return $this;
    }

    /**
     * Use toggle switch display style.
     */
    public function toggle(): static
    {
        $this->displayStyle = 'toggle';
        $this->configureForm();

        return $this;
    }

    /**
     * Use radio buttons display style.
     */
    public function radio(): static
    {
        $this->displayStyle = 'radio';
        $this->configureForm();

        return $this;
    }

    /**
     * Use select dropdown display style (default).
     */
    public function select(): static
    {
        $this->displayStyle = 'select';
        $this->configureForm();

        return $this;
    }

    /**
     * Set the number of columns for radio button layout.
     */
    public function columns(array|int|null $columns = 3): static
    {
        $this->radioColumns = $columns;
        $this->configureForm();

        return $this;
    }

    /**
     * Configure form component based on display style.
     */
    protected function configureForm(): void
    {
        $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));

        $options = [
            '' => $this->allLabel,
            '1' => $this->trueLabel,
            '0' => $this->falseLabel,
        ];

        $formComponent = match ($this->displayStyle) {
            'toggle' => Toggle::make('value')
                ->label($label)
                ->inline(false)
                ->columnSpanFull(),

            'radio' => Radio::make('value')
                ->label($label)
                ->options($options)
                ->default('')
                ->inline()
                ->columns($this->radioColumns)
                ->columnSpanFull(),

            default => Select::make('value')
                ->label($label)
                ->options($options)
                ->default('')
                ->native(false)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.boolean.placeholder'))
                ->columnSpanFull(),
        };

        $this->form([$formComponent]);
        $this->configureQuery();
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            $column = $this->getName();

            if ($value === '0' || $value === 0 || $value === false) {
                return $query->where($column, false);
            }

            if ($value === '1' || $value === 1 || $value === true) {
                return $query->where($column, true);
            }

            return $query;
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));

            $valueLabel = match ((string) $value) {
                '1', 'true' => $this->trueLabel,
                '0', 'false' => $this->falseLabel,
                default => $value,
            };

            return [
                Indicator::make("{$label}: {$valueLabel}")
                    ->removeField('value'),
            ];
        });
    }

    /**
     * Quick method to create yes/no filter.
     */
    public static function yesNo(?string $name = null): static
    {
        return static::make($name)
            ->trueLabel(__('filament-dcat-filters::filament-dcat-filters.boolean.yes'))
            ->falseLabel(__('filament-dcat-filters::filament-dcat-filters.boolean.no'));
    }

    /**
     * Quick method to create active/inactive filter.
     */
    public static function active(?string $name = 'is_active'): static
    {
        return static::make($name)
            ->trueLabel(__('filament-dcat-filters::filament-dcat-filters.boolean.active'))
            ->falseLabel(__('filament-dcat-filters::filament-dcat-filters.boolean.inactive'));
    }

    /**
     * Quick method to create enabled/disabled filter.
     */
    public static function enabled(?string $name = 'is_enabled'): static
    {
        return static::make($name)
            ->trueLabel(__('filament-dcat-filters::filament-dcat-filters.boolean.enabled'))
            ->falseLabel(__('filament-dcat-filters::filament-dcat-filters.boolean.disabled'));
    }

    /**
     * Quick method to create published/draft filter.
     */
    public static function published(?string $name = 'is_published'): static
    {
        return static::make($name)
            ->trueLabel(__('filament-dcat-filters::filament-dcat-filters.boolean.published'))
            ->falseLabel(__('filament-dcat-filters::filament-dcat-filters.boolean.draft'));
    }
}
