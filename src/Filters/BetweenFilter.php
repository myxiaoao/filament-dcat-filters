<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Closure;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\HtmlString;

/**
 * BetweenFilter is an alias for RangeFilter with integer type.
 *
 * This provides a simpler API for numeric range filtering,
 * matching the Dcat Admin API style.
 */
class BetweenFilter extends RangeFilter
{
    /**
     * Create a new between filter instance with integer type.
     */
    public static function make(?string $name = null): static
    {
        $filter = parent::make($name);

        // Set range type without calling integer() to avoid default form
        $filter->rangeType = 'integer';
        $filter->columnSpan(1);

        // Build the custom form
        $filter->buildBetweenForm();

        return $filter;
    }

    /**
     * Build the custom form layout.
     */
    protected function buildBetweenForm(): void
    {
        $label = $this->getLabel() ?? ucfirst($this->getName());

        $fromInput = TextInput::make('from')
            ->label($label)
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.range.from'))
            ->numeric();

        $toInput = TextInput::make('to')
            ->label(new HtmlString('&nbsp;'))
            ->placeholder(__('filament-dcat-filters::filament-dcat-filters.range.to'))
            ->numeric();

        // Apply appropriate validation based on range type
        if ($this->rangeType === 'integer') {
            $fromInput->integer();
            $toInput->integer();
        } else {
            $fromInput->step('any');
            $toInput->step('any');
        }

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    $fromInput,
                    $toInput,
                ]),
        ]);

        $this->configureQuery();
    }

    /**
     * Override numeric() to prevent parent's form override.
     */
    public function numeric(): static
    {
        $this->rangeType = 'numeric';
        $this->buildBetweenForm();

        return $this;
    }

    /**
     * Override integer() to prevent parent's form override.
     */
    public function integer(): static
    {
        $this->rangeType = 'integer';
        $this->buildBetweenForm();

        return $this;
    }

    /**
     * Override to return BetweenFilter type.
     */
    public function label(Htmlable|Closure|string|null $label): static
    {
        return parent::label($label);
    }
}
