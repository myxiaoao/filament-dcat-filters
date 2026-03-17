<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class InputMaskFilter extends Filter
{
    use HasColumnName;
    use HasInlineLabel;
    use HasLabelResolver;

    protected ?string $inputMask = null;

    protected ?string $placeholder = null;

    protected string $operator = 'like';

    protected bool $stripMaskOnQuery = true;

    protected ?string $stripPattern = null;

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
        $this->configureForm();
    }

    public function mask(string $mask): static
    {
        $this->inputMask = $mask;

        return $this;
    }

    public function placeholder(?string $placeholder): static
    {
        $this->placeholder = $placeholder;

        return $this;
    }

    public function operator(string $operator): static
    {
        $this->operator = $operator;

        return $this;
    }

    public function exact(): static
    {
        $this->operator = '=';

        return $this;
    }

    public function like(): static
    {
        $this->operator = 'like';

        return $this;
    }

    public function stripMask(bool $condition = true): static
    {
        $this->stripMaskOnQuery = $condition;

        return $this;
    }

    public function stripPattern(string $pattern): static
    {
        $this->stripPattern = $pattern;
        $this->stripMaskOnQuery = true;

        return $this;
    }

    public function phone(?string $format = null): static
    {
        return $this->mask($format ?? '(999) 999-9999')
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.phone'))
            ->stripPattern('/[^0-9]/');
    }

    public function chinaPhone(): static
    {
        return $this->mask('999 9999 9999')
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.china_phone'))
            ->stripPattern('/[^0-9]/');
    }

    public function creditCard(): static
    {
        return $this->mask('9999 9999 9999 9999')
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.credit_card'))
            ->stripPattern('/[^0-9]/');
    }

    public function date(?string $format = null): static
    {
        return $this->mask($format ?? '9999-99-99')
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.date'));
    }

    public function time(?string $format = null): static
    {
        return $this->mask($format ?? '99:99')
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.time'));
    }

    public function ip(): static
    {
        return $this->mask('999.999.999.999')
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.ip'));
    }

    public function zipCode(?string $format = null): static
    {
        return $this->mask($format ?? '99999')
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.input_mask.zip_code'))
            ->stripPattern('/[^0-9]/');
    }

    public function currency(?string $prefix = '$'): static
    {
        return $this->placeholder($prefix.'0.00')
            ->stripPattern('/[^0-9.]/');
    }

    protected function configureForm(): void
    {
        $labelResolver = $this->labelResolver();

        $input = TextInput::make('value')
            ->label($labelResolver)
            ->placeholder($this->placeholder ?? __('filament-dcat-filters::filament-dcat-filters.input_mask.placeholder'))
            ->columnSpanFull();

        if ($this->inputMask) {
            $input->mask($this->inputMask);
        }

        $this->applyInlineLabel($input, $labelResolver);

        $this->form([$input]);
        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            $column = $this->resolveColumnName();

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

            $label = $this->resolveLabel();

            return [
                Indicator::make("{$label}: {$value}")
                    ->removeField('value'),
            ];
        });
    }

    protected function stripMaskCharacters(string $value): string
    {
        if ($this->stripPattern) {
            return preg_replace($this->stripPattern, '', $value);
        }

        return preg_replace('/[^a-zA-Z0-9]/', '', $value);
    }
}
