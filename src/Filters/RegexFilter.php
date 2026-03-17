<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasDatabaseDriver;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class RegexFilter extends Filter
{
    use HasColumnName;
    use HasDatabaseDriver;
    use HasInlineLabel;
    use HasLabelResolver;

    protected ?string $regexPattern = null;

    protected bool $caseSensitive = true;

    protected ?string $placeholder = null;

    protected bool $patternMode = false;

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
        $labelResolver = $this->labelResolver();

        if ($this->patternMode && $this->regexPattern) {
            $this->form([
                Toggle::make('enabled')
                    ->label($labelResolver)
                    ->columnSpanFull(),
            ]);
        } else {
            $component = TextInput::make('pattern')
                ->label($labelResolver)
                ->placeholder($this->placeholder ?? __('filament-dcat-filters::filament-dcat-filters.regex.placeholder'))
                ->columnSpanFull();

            $this->applyInlineLabel($component, $labelResolver);

            $this->form([
                $component,
            ]);
        }

        $this->configureQuery();
    }

    /**
     * Apply a regex WHERE clause that adapts to the database driver.
     * PostgreSQL uses ~ (case-sensitive) or ~* (case-insensitive).
     * MySQL uses REGEXP with optional (?i) prefix.
     */
    protected function applyRegexWhere(Builder $query, string $column, string $pattern): Builder
    {
        if ($this->isPostgres($query)) {
            $operator = $this->caseSensitive ? '~' : '~*';

            return $query->whereRaw(
                $query->getGrammar()->wrap($column).' '.$operator.' ?',
                [$pattern]
            );
        }

        // MySQL/SQLite: use REGEXP with optional (?i) prefix
        $finalPattern = $this->caseSensitive ? $pattern : '(?i)'.$pattern;

        return $query->whereRaw(
            $query->getGrammar()->wrap($column).' REGEXP ?',
            [$finalPattern]
        );
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $column = $this->resolveColumnName();

            if ($this->patternMode && $this->regexPattern) {
                if (empty($data['enabled'])) {
                    return $query;
                }

                return $this->applyRegexWhere($query, $column, $this->regexPattern);
            }

            $pattern = $data['pattern'] ?? null;

            if ($pattern === null || $pattern === '') {
                return $query;
            }

            return $this->applyRegexWhere($query, $column, $pattern);
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->resolveLabel();

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
