<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class JsonFilter extends Filter
{
    protected ?string $jsonPath = null;

    protected string $operator = '=';

    protected ?string $defaultValue = null;

    protected array $validOperators = ['=', '!=', '>', '>=', '<', '<=', 'like', 'not like'];

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
        $this->configureForm();
    }

    /**
     * Set the JSON path to query (e.g., 'settings.theme' or 'metadata->preferences->language').
     */
    public function path(string $path): static
    {
        $this->jsonPath = $path;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the comparison operator.
     */
    public function operator(string $operator): static
    {
        if (! in_array(strtolower($operator), $this->validOperators)) {
            throw new \InvalidArgumentException("Invalid operator: {$operator}. Valid operators are: " . implode(', ', $this->validOperators));
        }

        $this->operator = strtolower($operator);
        $this->configureForm();

        return $this;
    }

    /**
     * Set default value.
     */
    public function defaultValue(mixed $value): static
    {
        $this->defaultValue = $value;
        $this->configureForm();

        return $this;
    }

    /**
     * Use equals operator.
     */
    public function eq(): static
    {
        return $this->operator('=');
    }

    /**
     * Use not equals operator.
     */
    public function neq(): static
    {
        return $this->operator('!=');
    }

    /**
     * Use greater than operator.
     */
    public function gt(): static
    {
        return $this->operator('>');
    }

    /**
     * Use greater than or equal operator.
     */
    public function gte(): static
    {
        return $this->operator('>=');
    }

    /**
     * Use less than operator.
     */
    public function lt(): static
    {
        return $this->operator('<');
    }

    /**
     * Use less than or equal operator.
     */
    public function lte(): static
    {
        return $this->operator('<=');
    }

    /**
     * Use like operator.
     */
    public function like(): static
    {
        return $this->operator('like');
    }

    /**
     * Use not like operator.
     */
    public function notLike(): static
    {
        return $this->operator('not like');
    }

    /**
     * Build the JSON column accessor for the query.
     */
    protected function buildJsonAccessor(): string
    {
        $column = $this->getName();
        $path = $this->jsonPath;

        if (! $path) {
            return $column;
        }

        // Convert dot notation to arrow notation if needed
        if (str_contains($path, '.') && ! str_contains($path, '->')) {
            $path = str_replace('.', '->', $path);
        }

        return "{$column}->{$path}";
    }

    /**
     * Configure form component.
     */
    protected function configureForm(): void
    {
        $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));
        if ($this->jsonPath) {
            $label .= ' (' . $this->jsonPath . ')';
        }

        $this->form([
            TextInput::make('value')
                ->label($label)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.json.placeholder'))
                ->default($this->defaultValue)
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
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            $jsonAccessor = $this->buildJsonAccessor();

            // Handle LIKE operators
            if (in_array($this->operator, ['like', 'not like'])) {
                $value = "%{$value}%";
            }

            return $query->whereRaw("{$jsonAccessor} {$this->operator} ?", [$value]);
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));
            $operatorLabel = $this->getOperatorLabel();

            return [
                Indicator::make("{$label} {$operatorLabel} \"{$value}\"")
                    ->removeField('value'),
            ];
        });
    }

    /**
     * Get human-readable operator label.
     */
    protected function getOperatorLabel(): string
    {
        return match ($this->operator) {
            '=' => '=',
            '!=' => '≠',
            '>' => '>',
            '>=' => '≥',
            '<' => '<',
            '<=' => '≤',
            'like' => __('filament-dcat-filters::filament-dcat-filters.json.contains'),
            'not like' => __('filament-dcat-filters::filament-dcat-filters.json.not_contains'),
            default => $this->operator,
        };
    }
}
