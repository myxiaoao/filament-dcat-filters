<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Carbon\Carbon;
use Cooper\FilamentDcatFilters\Concerns\HasRangeQuery;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class RangeFilter extends Filter
{
    use HasRangeQuery;

    protected string $rangeType = 'numeric';

    protected ?string $dateFormat = null;

    protected array $placeholders = [];

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    /**
     * Create a new range filter for date selection.
     */
    public function date(): static
    {
        $this->rangeType = 'date';
        $this->dateFormat = config('filament-dcat-filters.range.date_format', 'Y-m-d');

        $label = $this->getLabel() ?? ucfirst($this->getName());

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    DatePicker::make('from')
                        ->label($label)
                        ->placeholder($this->placeholders['from'] ?? __('filament-dcat-filters::filament-dcat-filters.range.from'))
                        ->format($this->dateFormat)
                        ->native(false),
                    DatePicker::make('to')
                        ->label(new \Illuminate\Support\HtmlString('&nbsp;'))
                        ->placeholder($this->placeholders['to'] ?? __('filament-dcat-filters::filament-dcat-filters.range.to'))
                        ->format($this->dateFormat)
                        ->native(false),
                ]),
        ]);

        $this->configureQuery();

        return $this;
    }

    /**
     * Create a new range filter for datetime selection.
     */
    public function datetime(?string $format = null): static
    {
        $this->rangeType = 'datetime';
        $this->dateFormat = $format ?? config('filament-dcat-filters.range.datetime_format', 'Y-m-d H:i:s');

        $label = $this->getLabel() ?? ucfirst($this->getName());

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    DateTimePicker::make('from')
                        ->label($label)
                        ->placeholder($this->placeholders['from'] ?? __('filament-dcat-filters::filament-dcat-filters.range.from'))
                        ->format($this->dateFormat)
                        ->seconds(str_contains($this->dateFormat, ':s'))
                        ->native(false),
                    DateTimePicker::make('to')
                        ->label(new \Illuminate\Support\HtmlString('&nbsp;'))
                        ->placeholder($this->placeholders['to'] ?? __('filament-dcat-filters::filament-dcat-filters.range.to'))
                        ->format($this->dateFormat)
                        ->seconds(str_contains($this->dateFormat, ':s'))
                        ->native(false),
                ]),
        ]);

        $this->configureQuery();

        return $this;
    }

    /**
     * Create a new range filter for time selection.
     */
    public function time(): static
    {
        $this->rangeType = 'time';
        $this->dateFormat = config('filament-dcat-filters.range.time_format', 'H:i:s');

        $label = $this->getLabel() ?? ucfirst($this->getName());

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    TimePicker::make('from')
                        ->label($label)
                        ->placeholder($this->placeholders['from'] ?? __('filament-dcat-filters::filament-dcat-filters.range.from'))
                        ->seconds(str_contains($this->dateFormat, ':s'))
                        ->native(false),
                    TimePicker::make('to')
                        ->label(new \Illuminate\Support\HtmlString('&nbsp;'))
                        ->placeholder($this->placeholders['to'] ?? __('filament-dcat-filters::filament-dcat-filters.range.to'))
                        ->seconds(str_contains($this->dateFormat, ':s'))
                        ->native(false),
                ]),
        ]);

        $this->configureQuery();

        return $this;
    }

    /**
     * Create a new range filter for numeric input.
     */
    public function numeric(): static
    {
        $this->rangeType = 'numeric';

        $label = $this->getLabel() ?? ucfirst($this->getName());

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('from')
                        ->label($label)
                        ->placeholder($this->placeholders['from'] ?? __('filament-dcat-filters::filament-dcat-filters.range.from'))
                        ->numeric()
                        ->step('any'),
                    TextInput::make('to')
                        ->label(new \Illuminate\Support\HtmlString('&nbsp;'))
                        ->placeholder($this->placeholders['to'] ?? __('filament-dcat-filters::filament-dcat-filters.range.to'))
                        ->numeric()
                        ->step('any'),
                ]),
        ]);

        $this->configureQuery();

        return $this;
    }

    /**
     * Create a new range filter for integer input.
     */
    public function integer(): static
    {
        $this->rangeType = 'integer';

        $label = $this->getLabel() ?? ucfirst($this->getName());

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('from')
                        ->label($label)
                        ->placeholder($this->placeholders['from'] ?? __('filament-dcat-filters::filament-dcat-filters.range.from'))
                        ->numeric()
                        ->integer(),
                    TextInput::make('to')
                        ->label(new \Illuminate\Support\HtmlString('&nbsp;'))
                        ->placeholder($this->placeholders['to'] ?? __('filament-dcat-filters::filament-dcat-filters.range.to'))
                        ->numeric()
                        ->integer(),
                ]),
        ]);

        $this->configureQuery();

        return $this;
    }

    /**
     * Set custom placeholders for from/to inputs.
     */
    public function placeholders(string|array $from, ?string $to = null): static
    {
        if (is_array($from)) {
            $this->placeholders = $from;
        } else {
            $this->placeholders = ['from' => $from, 'to' => $to];
        }

        return $this;
    }

    /**
     * Get placeholder text for the specified field.
     */
    protected function getPlaceholder(string $field): string
    {
        $baseLabel = $this->getLabel() ?? ucfirst($this->getName());

        if (isset($this->placeholders[$field])) {
            return "{$baseLabel} - {$this->placeholders[$field]}";
        }

        $defaultPlaceholder = config("filament-dcat-filters.range.placeholders.{$field}", ucfirst($field));

        return "{$baseLabel} - {$defaultPlaceholder}";
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $column = $this->getName();

            return $this->applyRangeQuery($query, $column, $data);
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->getLabel() ?? $this->getName();
            $indicators = [];

            foreach ($this->generateRangeIndicators($data, $label) as $text) {
                $indicators[] = Indicator::make($text);
            }

            return $indicators;
        });
    }

    /**
     * Convert to timestamp (useful for datetime columns stored as integers).
     */
    public function toTimestamp(): static
    {
        $this->query(function (Builder $query, array $data): Builder {
            $column = $this->getName();
            $from = $data['from'] ?? null;
            $to = $data['to'] ?? null;

            if ($from) {
                $from = Carbon::parse($from)->timestamp;
            }

            if ($to) {
                $to = Carbon::parse($to)->timestamp;
            }

            return $this->applyRangeQuery($query, $column, [
                'from' => $from,
                'to' => $to,
            ]);
        });

        return $this;
    }

    /**
     * Set custom date format for date/datetime filters.
     */
    public function format(string $format): static
    {
        $this->dateFormat = $format;

        return $this;
    }
}
