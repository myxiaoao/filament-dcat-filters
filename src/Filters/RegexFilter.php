<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class RegexFilter extends Filter
{
    protected ?string $regexPattern = null;

    protected bool $caseSensitive = true;

    protected ?string $placeholder = null;

    protected bool $patternMode = false;

    protected ?string $columnName = null;

    /**
     * Set the column name for the comparison.
     * This allows the filter name to differ from the actual database column.
     */
    public function column(string $column): static
    {
        $this->columnName = $column;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
        $this->configureForm();
    }

    public function pattern(string $pattern): static
    {
        $this->regexPattern = $pattern;
        $this->patternMode = true;
        $this->configureForm();

        return $this;
    }

    public function userPattern(): static
    {
        $this->patternMode = false;
        $this->configureForm();

        return $this;
    }

    public function caseInsensitive(bool $condition = true): static
    {
        $this->caseSensitive = ! $condition;

        return $this;
    }

    public function caseSensitive(bool $condition = true): static
    {
        $this->caseSensitive = $condition;

        return $this;
    }

    public function placeholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function chinaMobile(): static
    {
        return $this->pattern('^1[3-9][0-9]{9}$')
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.regex.china_mobile'));
    }

    public function email(): static
    {
        return $this->pattern('^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$')
            ->caseInsensitive()
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.regex.email'));
    }

    public function url(): static
    {
        return $this->pattern('^https?://[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}')
            ->caseInsensitive()
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.regex.url'));
    }

    public function ipv4(): static
    {
        return $this->pattern('^([0-9]{1,3}\\.){3}[0-9]{1,3}$')
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.regex.ipv4'));
    }

    protected function configureForm(): void
    {
        $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));

        if ($this->patternMode && $this->regexPattern) {
            $this->form([
                Toggle::make('enabled')
                    ->label($label)
                    ->columnSpanFull(),
            ]);
        } else {
            $this->form([
                TextInput::make('pattern')
                    ->label($label)
                    ->placeholder($this->placeholder ?? __('filament-dcat-filters::filament-dcat-filters.regex.placeholder'))
                    ->columnSpanFull(),
            ]);
        }

        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $column = $this->columnName ?? $this->getName();

            if ($this->patternMode && $this->regexPattern) {
                if (empty($data['enabled'])) {
                    return $query;
                }

                $pattern = $this->caseSensitive ? $this->regexPattern : '(?i)'.$this->regexPattern;

                return $query->whereRaw("{$column} REGEXP ?", [$pattern]);
            }

            $pattern = $data['pattern'] ?? null;

            if ($pattern === null || $pattern === '') {
                return $query;
            }

            $finalPattern = $this->caseSensitive ? $pattern : '(?i)'.$pattern;

            return $query->whereRaw("{$column} REGEXP ?", [$finalPattern]);
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));

            if ($this->patternMode && $this->regexPattern) {
                if (empty($data['enabled'])) {
                    return [];
                }

                return [
                    Indicator::make("{$label}: ".__('filament-dcat-filters::filament-dcat-filters.regex.pattern_applied'))
                        ->removeField('enabled'),
                ];
            }

            $pattern = $data['pattern'] ?? null;

            if ($pattern === null || $pattern === '') {
                return [];
            }

            return [
                Indicator::make("{$label}: /{$pattern}/")
                    ->removeField('pattern'),
            ];
        });
    }
}
