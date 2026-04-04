<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class ColumnCompareFilter extends Filter
{
    use HasFilterState;
    use HasInlineLabel;
    use HasLabelResolver;

    protected const ALLOWED_OPERATORS = ['=', '!=', '>', '>=', '<', '<='];

    protected ?string $leftColumn = null;

    protected ?string $rightColumn = null;

    protected string $operator = '>';

    protected bool $selectMode = false;

    protected function describeState(): FilterStateDescriptor
    {
        if ($this->selectMode) {
            return FilterStateDescriptor::make()
                ->fields(['operator'])
                ->type(StateType::Keyed)
                ->capabilities(['indicator'])
                ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
        }

        return FilterStateDescriptor::make()
            ->fields(['enabled'])
            ->type(StateType::Toggle)
            ->emptyWhen(fn (array $data) => empty($data['enabled']))
            ->capabilities(['indicator'])
            ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    public function leftColumn(string $column): static
    {
        $this->leftColumn = $column;
        $this->configureForm();

        return $this;
    }

    public function rightColumn(string $column): static
    {
        $this->rightColumn = $column;
        $this->configureForm();

        return $this;
    }

    public static function comparing(string $left, string $right): static
    {
        return static::make("{$left}_vs_{$right}")
            ->leftColumn($left)
            ->rightColumn($right);
    }

    // Operator setters (for toggle mode)
    public function gt(): static
    {
        $this->operator = '>';

        return $this;
    }

    public function gte(): static
    {
        $this->operator = '>=';

        return $this;
    }

    public function lt(): static
    {
        $this->operator = '<';

        return $this;
    }

    public function lte(): static
    {
        $this->operator = '<=';

        return $this;
    }

    public function eq(): static
    {
        $this->operator = '=';

        return $this;
    }

    public function ne(): static
    {
        $this->operator = '!=';

        return $this;
    }

    public function toggle(): static
    {
        $this->selectMode = false;
        $this->configureForm();

        return $this;
    }

    public function select(): static
    {
        $this->selectMode = true;
        $this->configureForm();

        return $this;
    }

    protected function configureForm(): void
    {
        if (! $this->leftColumn || ! $this->rightColumn) {
            return;
        }

        $labelResolver = $this->labelResolver();

        if ($this->selectMode) {
            $options = [];
            foreach (self::ALLOWED_OPERATORS as $op) {
                $options[$op] = "{$this->leftColumn} {$op} {$this->rightColumn}";
            }

            $component = Select::make('operator')
                ->label($labelResolver)
                ->options($options)
                ->native(false)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.column_compare.placeholder'))
                ->columnSpanFull();

            $this->applyInlineLabel($component, $labelResolver);
            $this->form([$component]);
        } else {
            $displayLabel = "{$this->leftColumn} {$this->operator} {$this->rightColumn}";
            $component = Toggle::make('enabled')
                ->label($displayLabel)
                ->columnSpanFull();

            $this->form([$component]);
        }

        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            if (! $this->leftColumn || ! $this->rightColumn) {
                return $query;
            }

            if ($this->selectMode) {
                $operator = $data['operator'] ?? null;

                if ($operator === null || $operator === '') {
                    return $query;
                }

                // Validate operator against whitelist
                if (! in_array($operator, self::ALLOWED_OPERATORS, true)) {
                    return $query;
                }

                return $query->whereColumn($this->leftColumn, $operator, $this->rightColumn);
            }

            // Toggle mode
            if (empty($data['enabled'])) {
                return $query;
            }

            return $query->whereColumn($this->leftColumn, $this->operator, $this->rightColumn);
        });

        $this->indicateUsing(function (array $data): array {
            if (! $this->leftColumn || ! $this->rightColumn) {
                return [];
            }

            if ($this->selectMode) {
                $operator = $data['operator'] ?? null;
                if ($operator === null || $operator === '') {
                    return [];
                }

                return [
                    Indicator::make("{$this->leftColumn} {$operator} {$this->rightColumn}")
                        ->removeField('operator'),
                ];
            }

            if (empty($data['enabled'])) {
                return [];
            }

            $label = $this->resolveLabel();

            return [
                Indicator::make("{$label}: {$this->leftColumn} {$this->operator} {$this->rightColumn}")
                    ->removeField('enabled'),
            ];
        });
    }

    public function getLeftColumn(): ?string
    {
        return $this->leftColumn;
    }

    public function getRightColumn(): ?string
    {
        return $this->rightColumn;
    }

    public function getOperator(): string
    {
        return $this->operator;
    }

    public function isSelectMode(): bool
    {
        return $this->selectMode;
    }
}
