<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class JsonFilter extends Filter
{
    use HasColumnName;
    use HasInlineLabel;

    protected const VALID_OPERATORS = ['=', '!=', '>', '>=', '<', '<=', 'like', 'not like'];

    protected ?string $jsonPath = null;

    protected string $operator = '=';

    protected ?string $defaultValue = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
        $this->configureForm();
    }

    public function path(string $path): static
    {
        $this->jsonPath = $path;

        return $this;
    }

    public function operator(string $operator): static
    {
        $operator = strtolower($operator);

        if (! in_array($operator, self::VALID_OPERATORS)) {
            throw new \InvalidArgumentException(
                "Invalid operator: {$operator}. Valid operators are: ".implode(', ', self::VALID_OPERATORS)
            );
        }

        $this->operator = $operator;
        $this->configureQuery();

        return $this;
    }

    public function defaultValue(mixed $value): static
    {
        $this->defaultValue = $value;

        return $this;
    }

    public function eq(): static
    {
        return $this->operator('=');
    }

    public function neq(): static
    {
        return $this->operator('!=');
    }

    public function gt(): static
    {
        return $this->operator('>');
    }

    public function gte(): static
    {
        return $this->operator('>=');
    }

    public function lt(): static
    {
        return $this->operator('<');
    }

    public function lte(): static
    {
        return $this->operator('<=');
    }

    public function like(): static
    {
        return $this->operator('like');
    }

    public function notLike(): static
    {
        return $this->operator('not like');
    }

    protected function buildJsonAccessor(): string
    {
        $column = $this->resolveColumnName();

        if (! $this->jsonPath) {
            return $column;
        }

        $path = str_contains($this->jsonPath, '->')
            ? $this->jsonPath
            : str_replace('.', '->', $this->jsonPath);

        return "{$column}->{$path}";
    }

    protected function configureForm(): void
    {
        $labelResolver = fn (): string => ($this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()))).($this->jsonPath ? " ({$this->jsonPath})" : '');

        $component = TextInput::make('value')
            ->label($labelResolver)
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.json.placeholder'))
            ->default($this->defaultValue)
            ->columnSpanFull();

        $this->applyInlineLabel($component, $labelResolver);

        $this->form([
            $component,
        ]);

        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            $jsonAccessor = $this->buildJsonAccessor();

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

            return [
                Indicator::make("{$label} {$this->getOperatorLabel()} \"{$value}\"")
                    ->removeField('value'),
            ];
        });
    }

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
