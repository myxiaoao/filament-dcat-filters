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

        $labelResolver = fn (): string => $this->getLabel() ?? ucfirst($this->getName());
        $displayFormat = config('filament-dcat-filters.range.date_display_format', 'M j, Y');

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    DatePicker::make('from')
                        ->label($labelResolver)
                        ->placeholder($this->placeholders['from'] ?? __('filament-dcat-filters::filament-dcat-filters.range.from'))
                        ->format($this->dateFormat)
                        ->displayFormat($displayFormat)
                        ->native(false)
                        ->live(onBlur: true)
                        ->maxDate(fn (callable $get): ?string => $get('to') ?: null),
                    DatePicker::make('to')
                        ->label(new \Illuminate\Support\HtmlString('&nbsp;'))
                        ->placeholder($this->placeholders['to'] ?? __('filament-dcat-filters::filament-dcat-filters.range.to'))
                        ->format($this->dateFormat)
                        ->displayFormat($displayFormat)
                        ->native(false)
                        ->live(onBlur: true)
                        ->minDate(fn (callable $get): ?string => $get('from') ?: null),
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

        $labelResolver = fn (): string => $this->getLabel() ?? ucfirst($this->getName());
        $displayFormat = config('filament-dcat-filters.range.datetime_display_format', 'M j, Y H:i');
        $hasSeconds = str_contains($this->dateFormat, ':s');
        $defaultEndTime = $hasSeconds ? '23:59:59' : '23:59:00';

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    DateTimePicker::make('from')
                        ->label($labelResolver)
                        ->placeholder($this->placeholders['from'] ?? __('filament-dcat-filters::filament-dcat-filters.range.from'))
                        ->format($this->dateFormat)
                        ->displayFormat($displayFormat)
                        ->seconds($hasSeconds)
                        ->native(false)
                        ->live(onBlur: true)
                        ->maxDate(fn (callable $get): ?string => $get('to') ?: null),
                    DateTimePicker::make('to')
                        ->label(new \Illuminate\Support\HtmlString('&nbsp;'))
                        ->placeholder($this->placeholders['to'] ?? __('filament-dcat-filters::filament-dcat-filters.range.to'))
                        ->format($this->dateFormat)
                        ->displayFormat($displayFormat)
                        ->seconds($hasSeconds)
                        ->native(false)
                        ->live()
                        ->minDate(fn (callable $get): ?string => $get('from') ?: null)
                        ->afterStateUpdated(function (?string $state, callable $set) use ($defaultEndTime): void {
                            if ($state === null || $state === '') {
                                return;
                            }

                            try {
                                $datetime = Carbon::parse($state);

                                // If time is 00:00:00, set it to end of day
                                if ($datetime->format('H:i:s') === '00:00:00') {
                                    $set('to', $datetime->format('Y-m-d').' '.$defaultEndTime);
                                }
                            } catch (\Exception $e) {
                                // Ignore parse errors
                            }
                        }),
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

        $labelResolver = fn (): string => $this->getLabel() ?? ucfirst($this->getName());

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    TimePicker::make('from')
                        ->label($labelResolver)
                        ->placeholder($this->placeholders['from'] ?? __('filament-dcat-filters::filament-dcat-filters.range.from'))
                        ->seconds(str_contains($this->dateFormat, ':s'))
                        ->native(false)
                        ->live(onBlur: true),
                    TimePicker::make('to')
                        ->label(new \Illuminate\Support\HtmlString('&nbsp;'))
                        ->placeholder($this->placeholders['to'] ?? __('filament-dcat-filters::filament-dcat-filters.range.to'))
                        ->seconds(str_contains($this->dateFormat, ':s'))
                        ->native(false)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function ($state, callable $get, callable $set): void {
                            $from = $get('from');
                            if ($from && $state && $state < $from) {
                                $set('to', $from);
                            }
                        }),
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

        $labelResolver = fn (): string => $this->getLabel() ?? ucfirst($this->getName());

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('from')
                        ->label($labelResolver)
                        ->placeholder($this->placeholders['from'] ?? __('filament-dcat-filters::filament-dcat-filters.range.from'))
                        ->numeric()
                        ->step('any')
                        ->live(onBlur: true)
                        ->maxValue(fn (callable $get): ?float => $get('to') !== null && $get('to') !== '' ? (float) $get('to') : null),
                    TextInput::make('to')
                        ->label(new \Illuminate\Support\HtmlString('&nbsp;'))
                        ->placeholder($this->placeholders['to'] ?? __('filament-dcat-filters::filament-dcat-filters.range.to'))
                        ->numeric()
                        ->step('any')
                        ->live(onBlur: true)
                        ->minValue(fn (callable $get): ?float => $get('from') !== null && $get('from') !== '' ? (float) $get('from') : null),
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

        $labelResolver = fn (): string => $this->getLabel() ?? ucfirst($this->getName());

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([
                    TextInput::make('from')
                        ->label($labelResolver)
                        ->placeholder($this->placeholders['from'] ?? __('filament-dcat-filters::filament-dcat-filters.range.from'))
                        ->numeric()
                        ->integer()
                        ->live(onBlur: true)
                        ->maxValue(fn (callable $get): ?int => $get('to') !== null && $get('to') !== '' ? (int) $get('to') : null),
                    TextInput::make('to')
                        ->label(new \Illuminate\Support\HtmlString('&nbsp;'))
                        ->placeholder($this->placeholders['to'] ?? __('filament-dcat-filters::filament-dcat-filters.range.to'))
                        ->numeric()
                        ->integer()
                        ->live(onBlur: true)
                        ->minValue(fn (callable $get): ?int => $get('from') !== null && $get('from') !== '' ? (int) $get('from') : null),
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
            $column = $this->columnName ?? $this->getName();

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
            $column = $this->columnName ?? $this->getName();
            $from = $data['from'] ?? null;
            $to = $data['to'] ?? null;

            try {
                if ($from) {
                    $from = Carbon::parse($from)->timestamp;
                }

                if ($to) {
                    $to = Carbon::parse($to)->timestamp;
                }
            } catch (\Exception $e) {
                report($e);

                return $query;
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
