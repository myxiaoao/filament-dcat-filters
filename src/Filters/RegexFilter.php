<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class RegexFilter extends Filter
{
    protected ?string $regexPattern = null;

    protected bool $caseSensitive = true;

    protected ?string $placeholder = null;

    protected bool $patternMode = false;

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
     * Set a fixed regex pattern to match against.
     * When set, the filter will check if values match this pattern.
     */
    public function pattern(string $pattern): static
    {
        $this->regexPattern = $pattern;
        $this->patternMode = true;
        $this->configureForm();

        return $this;
    }

    /**
     * Allow users to input their own regex pattern.
     * This is the default mode.
     */
    public function userPattern(): static
    {
        $this->patternMode = false;
        $this->configureForm();

        return $this;
    }

    /**
     * Make the regex case-insensitive.
     */
    public function caseInsensitive(bool $condition = true): static
    {
        $this->caseSensitive = ! $condition;
        $this->configureForm();

        return $this;
    }

    /**
     * Make the regex case-sensitive (default).
     */
    public function caseSensitive(bool $condition = true): static
    {
        $this->caseSensitive = $condition;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the placeholder text.
     */
    public function placeholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;
        $this->configureForm();

        return $this;
    }

    /**
     * Common preset: China mobile phone number pattern.
     */
    public function chinaMobile(): static
    {
        return $this->pattern('^1[3-9][0-9]{9}$')
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.regex.china_mobile'));
    }

    /**
     * Common preset: Email pattern.
     */
    public function email(): static
    {
        return $this->pattern('^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}$')
            ->caseInsensitive()
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.regex.email'));
    }

    /**
     * Common preset: URL pattern.
     */
    public function url(): static
    {
        return $this->pattern('^https?://[a-zA-Z0-9.-]+\\.[a-zA-Z]{2,}')
            ->caseInsensitive()
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.regex.url'));
    }

    /**
     * Common preset: IPv4 address pattern.
     */
    public function ipv4(): static
    {
        return $this->pattern('^([0-9]{1,3}\\.){3}[0-9]{1,3}$')
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.regex.ipv4'));
    }

    /**
     * Configure form component.
     */
    protected function configureForm(): void
    {
        $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));

        if ($this->patternMode && $this->regexPattern) {
            // Pattern mode: boolean toggle to apply the pattern
            $this->form([
                \Filament\Forms\Components\Toggle::make('enabled')
                    ->label($label)
                    ->columnSpanFull(),
            ]);
        } else {
            // User input mode: text input for regex pattern
            $placeholder = $this->placeholder
                ?? __('filament-dcat-filters::filament-dcat-filters.regex.placeholder');

            $this->form([
                TextInput::make('pattern')
                    ->label($label)
                    ->placeholder($placeholder)
                    ->columnSpanFull(),
            ]);
        }

        $this->configureQuery();
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $column = $this->getName();

            if ($this->patternMode && $this->regexPattern) {
                // Pattern mode: apply fixed pattern when enabled
                $enabled = $data['enabled'] ?? false;

                if (! $enabled) {
                    return $query;
                }

                $regexOperator = $this->caseSensitive ? 'REGEXP' : 'REGEXP';
                $pattern = $this->caseSensitive
                    ? $this->regexPattern
                    : '(?i)' . $this->regexPattern;

                return $query->whereRaw("{$column} {$regexOperator} ?", [$pattern]);
            }

            // User pattern mode
            $pattern = $data['pattern'] ?? null;

            if ($pattern === null || $pattern === '') {
                return $query;
            }

            $regexOperator = $this->caseSensitive ? 'REGEXP' : 'REGEXP';
            $finalPattern = $this->caseSensitive
                ? $pattern
                : '(?i)' . $pattern;

            return $query->whereRaw("{$column} {$regexOperator} ?", [$finalPattern]);
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));

            if ($this->patternMode && $this->regexPattern) {
                $enabled = $data['enabled'] ?? false;

                if (! $enabled) {
                    return [];
                }

                return [
                    Indicator::make("{$label}: " . __('filament-dcat-filters::filament-dcat-filters.regex.pattern_applied'))
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
