<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Carbon\Carbon;
use Closure;
use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Grid;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\HtmlString;

class TimezoneAwareDateFilter extends Filter
{
    use HasColumnName;
    use HasFilterState;
    use HasInlineLabel;
    use HasLabelResolver;

    protected string|Closure $userTimezone = 'UTC';

    protected string $databaseTimezone = 'UTC';

    protected bool $isDatetime = false;

    protected function describeState(): FilterStateDescriptor
    {
        return FilterStateDescriptor::make()
            ->fields(['from', 'to'])
            ->type(StateType::Range)
            ->capabilities(['indicator'])
            ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);
    }

    public function userTimezone(string|Closure $timezone): static
    {
        $this->userTimezone = $timezone;
        $this->configureForm();

        return $this;
    }

    public function databaseTimezone(string $timezone): static
    {
        $this->databaseTimezone = $timezone;

        return $this;
    }

    public function date(): static
    {
        $this->isDatetime = false;
        $this->configureForm();

        return $this;
    }

    public function datetime(): static
    {
        $this->isDatetime = true;
        $this->configureForm();

        return $this;
    }

    public function getUserTimezone(): string
    {
        if ($this->userTimezone instanceof Closure) {
            return ($this->userTimezone)() ?? 'UTC';
        }

        return $this->userTimezone;
    }

    public function getDatabaseTimezone(): string
    {
        return $this->databaseTimezone;
    }

    public function isDatetime(): bool
    {
        return $this->isDatetime;
    }

    protected function configureForm(): void
    {
        $labelResolver = $this->labelResolver();
        $dateFormat = config('filament-dcat-filters.range.date_format', 'Y-m-d');
        $displayFormat = config('filament-dcat-filters.range.date_display_format', 'Y-m-d');

        if ($this->isDatetime) {
            $datetimeFormat = config('filament-dcat-filters.range.datetime_format', 'Y-m-d H:i:s');
            $datetimeDisplayFormat = config('filament-dcat-filters.range.datetime_display_format', 'Y-m-d H:i:s');

            $from = DateTimePicker::make('from')
                ->label($labelResolver)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.timezone_date.from'))
                ->format($datetimeFormat)
                ->displayFormat($datetimeDisplayFormat)
                ->native(false)
                ->live(onBlur: true);

            $to = DateTimePicker::make('to')
                ->label(new HtmlString('&nbsp;'))
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.timezone_date.to'))
                ->format($datetimeFormat)
                ->displayFormat($datetimeDisplayFormat)
                ->native(false)
                ->live(onBlur: true);
        } else {
            $from = DatePicker::make('from')
                ->label($labelResolver)
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.timezone_date.from'))
                ->format($dateFormat)
                ->displayFormat($displayFormat)
                ->native(false)
                ->live(onBlur: true);

            $to = DatePicker::make('to')
                ->label(new HtmlString('&nbsp;'))
                ->placeholder(__('filament-dcat-filters::filament-dcat-filters.timezone_date.to'))
                ->format($dateFormat)
                ->displayFormat($displayFormat)
                ->native(false)
                ->live(onBlur: true);
        }

        /** @phpstan-ignore function.alreadyNarrowedType (defensive check — method comes from HasInlineLabel trait) */
        if (method_exists($this, 'applyRangeInlineLabels')) {
            $this->applyRangeInlineLabels($from, $to, $labelResolver);
        } else {
            $this->applyInlineLabel($from, $labelResolver);
        }

        $this->form([
            Grid::make(2)
                ->columnSpanFull()
                ->schema([$from, $to]),
        ]);

        $this->configureQuery();
    }

    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $fromInput = $data['from'] ?? null;
            $toInput = $data['to'] ?? null;

            if (($fromInput === null || $fromInput === '') && ($toInput === null || $toInput === '')) {
                return $query;
            }

            $column = $this->resolveColumnName();
            $userTz = $this->getUserTimezone();
            $dbTz = $this->databaseTimezone;

            if ($fromInput !== null && $fromInput !== '' && $toInput !== null && $toInput !== '') {
                $from = $this->convertToDbTimezone($fromInput, $userTz, $dbTz, true);
                $to = $this->convertToDbTimezone($toInput, $userTz, $dbTz, false);

                return $query->whereBetween($column, [$from, $to]);
            }

            if ($fromInput !== null && $fromInput !== '') {
                $from = $this->convertToDbTimezone($fromInput, $userTz, $dbTz, true);

                return $query->where($column, '>=', $from);
            }

            $to = $this->convertToDbTimezone($toInput, $userTz, $dbTz, false);

            return $query->where($column, '<=', $to);
        });

        $this->indicateUsing(function (array $data): array {
            $from = $data['from'] ?? null;
            $to = $data['to'] ?? null;

            if (($from === null || $from === '') && ($to === null || $to === '')) {
                return [];
            }

            $label = $this->resolveLabel();
            $tz = $this->getUserTimezone();
            $indicators = [];

            if ($from !== null && $from !== '') {
                $indicators[] = Indicator::make("{$label}: ".__('filament-dcat-filters::filament-dcat-filters.timezone_date.indicator_from', ['value' => $from, 'tz' => $tz]))
                    ->removeField('from');
            }

            if ($to !== null && $to !== '') {
                $indicators[] = Indicator::make("{$label}: ".__('filament-dcat-filters::filament-dcat-filters.timezone_date.indicator_to', ['value' => $to, 'tz' => $tz]))
                    ->removeField('to');
            }

            return $indicators;
        });
    }

    protected function convertToDbTimezone(string $value, string $userTz, string $dbTz, bool $isStart): string
    {
        $carbon = Carbon::parse($value, $userTz);

        if (! $this->isDatetime) {
            $carbon = $isStart ? $carbon->startOfDay() : $carbon->endOfDay();
        }

        return $carbon->setTimezone($dbTz)->format('Y-m-d H:i:s');
    }
}
