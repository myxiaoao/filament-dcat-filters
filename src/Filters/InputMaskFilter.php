<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class InputMaskFilter extends Filter
{
    protected ?string $inputMask = null;

    protected ?string $placeholder = null;

    protected string $operator = 'like';

    protected bool $stripMaskOnQuery = true;

    protected ?string $stripPattern = null;

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
     * Set the input mask pattern.
     * Uses alpinejs-mask format:
     * - 9: Numeric
     * - a: Alphabetical
     * - *: Alphanumeric
     */
    public function mask(string $mask): static
    {
        $this->inputMask = $mask;
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
     * Set the comparison operator.
     */
    public function operator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    /**
     * Use exact match operator.
     */
    public function exact(): static
    {
        $this->operator = '=';

        return $this;
    }

    /**
     * Use like match operator (default).
     */
    public function like(): static
    {
        $this->operator = 'like';

        return $this;
    }

    /**
     * Strip mask characters before querying database.
     */
    public function stripMask(bool $condition = true): static
    {
        $this->stripMaskOnQuery = $condition;

        return $this;
    }

    /**
     * Set a custom regex pattern for stripping characters.
     */
    public function stripPattern(string $pattern): static
    {
        $this->stripPattern = $pattern;
        $this->stripMaskOnQuery = true;

        return $this;
    }

    /**
     * Preset: Phone number mask.
     */
    public function phone(?string $format = null): static
    {
        $mask = $format ?? '(999) 999-9999';
        $this->mask($mask);
        $this->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.phone'));
        $this->stripPattern('/[^0-9]/');

        return $this;
    }

    /**
     * Preset: China phone number mask.
     */
    public function chinaPhone(): static
    {
        $this->mask('999 9999 9999');
        $this->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.china_phone'));
        $this->stripPattern('/[^0-9]/');

        return $this;
    }

    /**
     * Preset: Credit card mask.
     */
    public function creditCard(): static
    {
        $this->mask('9999 9999 9999 9999');
        $this->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.credit_card'));
        $this->stripPattern('/[^0-9]/');

        return $this;
    }

    /**
     * Preset: Date mask (YYYY-MM-DD).
     */
    public function date(?string $format = null): static
    {
        $mask = $format ?? '9999-99-99';
        $this->mask($mask);
        $this->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.date'));

        return $this;
    }

    /**
     * Preset: Time mask (HH:MM).
     */
    public function time(?string $format = null): static
    {
        $mask = $format ?? '99:99';
        $this->mask($mask);
        $this->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.time'));

        return $this;
    }

    /**
     * Preset: IP address mask.
     */
    public function ip(): static
    {
        $this->mask('999.999.999.999');
        $this->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.ip'));

        return $this;
    }

    /**
     * Preset: ZIP code mask (US).
     */
    public function zipCode(?string $format = null): static
    {
        $mask = $format ?? '99999';
        $this->mask($mask);
        $this->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.zip_code'));
        $this->stripPattern('/[^0-9]/');

        return $this;
    }

    /**
     * Preset: Currency mask.
     */
    public function currency(?string $prefix = '$'): static
    {
        $this->placeholder($prefix . '0.00');
        // Note: Currency requires special handling, using numeric type
        $this->stripPattern('/[^0-9.]/');

        return $this;
    }

    /**
     * Configure form component.
     */
    protected function configureForm(): void
    {
        $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));
        $placeholder = $this->placeholder
            ?? __('filament-dcat-filters::filament-dcat-filters.input_mask.placeholder');

        $input = TextInput::make('value')
            ->label($label)
            ->placeholder($placeholder)
            ->columnSpanFull();

        // Apply mask if set
        if ($this->inputMask) {
            $input->mask($this->inputMask);
        }

        $this->form([$input]);
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

            // Strip mask characters if enabled
            if ($this->stripMaskOnQuery) {
                $value = $this->stripMaskCharacters($value);
            }

            if ($this->operator === 'like') {
                return $query->where($column, 'like', "%{$value}%");
            }

            return $query->where($column, $this->operator, $value);
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $label = $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));

            return [
                Indicator::make("{$label}: {$value}")
                    ->removeField('value'),
            ];
        });
    }

    /**
     * Strip mask characters from value.
     */
    protected function stripMaskCharacters(string $value): string
    {
        if ($this->stripPattern) {
            return preg_replace($this->stripPattern, '', $value);
        }

        // Default: strip common mask characters
        return preg_replace('/[^a-zA-Z0-9]/', '', $value);
    }
}
