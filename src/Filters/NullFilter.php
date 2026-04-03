<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Cooper\FilamentDcatFilters\Concerns\HasSelectRadioDisplay;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class NullFilter extends Filter
{
    use HasColumnName;
    use HasFilterState;
    use HasInlineLabel;
    use HasLabelResolver;
    use HasSelectRadioDisplay;

    protected string $nullLabel = 'Is Null';

    protected string $notNullLabel = 'Is Not Null';

    protected string $allLabel = 'All';

    protected function describeState(): FilterStateDescriptor
    {
        return FilterStateDescriptor::make()
            ->fields(['value'])
            ->type(StateType::Single)
            ->emptyWhen(fn (array $data) => ($data['value'] ?? '') === '')
            ->capabilities(['indicator'])
            ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
    }

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->nullLabel = __('filament-dcat-filters::filament-dcat-filters.null.is_null');
        $this->notNullLabel = __('filament-dcat-filters::filament-dcat-filters.null.is_not_null');
        $this->allLabel = __('filament-dcat-filters::filament-dcat-filters.null.all');

        $this->columnSpan(1);
        $this->configureForm();
    }

    /**
     * Set the label for null value.
     */
    public function nullLabel(string $label): static
    {
        $this->nullLabel = $label;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the label for not null value.
     */
    public function notNullLabel(string $label): static
    {
        $this->notNullLabel = $label;
        $this->configureForm();

        return $this;
    }

    /**
     * Set the label for all/any value.
     */
    public function allLabel(string $label): static
    {
        $this->allLabel = $label;
        $this->configureForm();

        return $this;
    }

    /**
     * Configure form component based on display style.
     */
    protected function configureForm(): void
    {
        $labelResolver = $this->labelResolver();

        $options = [
            '' => $this->allLabel,
            'null' => $this->nullLabel,
            'not_null' => $this->notNullLabel,
        ];

        $formComponent = $this->buildFormComponent(
            'value',
            $labelResolver,
            $options,
            __('filament-dcat-filters::filament-dcat-filters.null.placeholder')
        );

        $this->form([$formComponent]);
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

            $column = $this->resolveColumnName();

            if ($value === 'null') {
                return $query->whereNull($column);
            }

            if ($value === 'not_null') {
                return $query->whereNotNull($column);
            }

            return $query;
        });

        $this->indicateUsing(function (array $data): array {
            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $label = $this->resolveLabel();

            $valueLabel = match ($value) {
                'null' => $this->nullLabel,
                'not_null' => $this->notNullLabel,
                default => $value,
            };

            return [
                Indicator::make("{$label}: {$valueLabel}")
                    ->removeField('value'),
            ];
        });
    }

    /**
     * Quick method to create deleted filter (for soft deletes).
     */
    public static function deleted(?string $name = 'deleted_at'): static
    {
        return static::make($name)
            ->nullLabel(__('filament-dcat-filters::filament-dcat-filters.null.not_deleted'))
            ->notNullLabel(__('filament-dcat-filters::filament-dcat-filters.null.deleted'));
    }

    /**
     * Quick method to create assigned filter.
     */
    public static function assigned(?string $name = null): static
    {
        return static::make($name)
            ->nullLabel(__('filament-dcat-filters::filament-dcat-filters.null.unassigned'))
            ->notNullLabel(__('filament-dcat-filters::filament-dcat-filters.null.assigned'));
    }

    /**
     * Quick method to create empty/filled filter.
     */
    public static function empty(?string $name = null): static
    {
        return static::make($name)
            ->nullLabel(__('filament-dcat-filters::filament-dcat-filters.null.empty'))
            ->notNullLabel(__('filament-dcat-filters::filament-dcat-filters.null.filled'));
    }
}
