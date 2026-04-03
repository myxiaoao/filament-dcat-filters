<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Cooper\FilamentDcatFilters\Concerns\HasRelationship;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class InFilter extends Filter
{
    use HasColumnName;
    use HasFilterState;
    use HasInlineLabel;
    use HasLabelResolver;
    use HasRelationship;

    protected array $options = [];

    protected bool $multiple = false;

    protected bool $searchable = false;

    protected bool $negate = false;

    protected function describeState(): FilterStateDescriptor
    {
        return FilterStateDescriptor::make()
            ->fields($this->multiple ? ['values'] : ['value'])
            ->type($this->multiple ? StateType::Multiple : StateType::Single)
            ->emptyWhen(fn (array $data) => $this->multiple
                ? empty($data['values'] ?? [])
                : ($data['value'] ?? '') === '')
            ->capabilities(array_filter(['relationship', 'indicator', $this->multiple ? 'multiple' : null]))
            ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
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
     * Set the available options for this filter.
     */
    public function options(array $options): static
    {
        $this->options = $options;
        $this->configureForm();

        return $this;
    }

    /**
     * Enable multiple selection (uses CheckboxList).
     */
    public function multiple(bool $multiple = true): static
    {
        $this->multiple = $multiple;
        $this->configureForm();

        return $this;
    }

    /**
     * Enable searchable dropdown.
     */
    public function searchable(bool $searchable = true): static
    {
        $this->searchable = $searchable;
        $this->configureForm();

        return $this;
    }

    /**
     * Negate the filter (NOT IN).
     */
    public function negate(): static
    {
        $this->negate = true;

        return $this;
    }

    /**
     * Alias for negate() - use NOT IN operator.
     */
    public function notIn(): static
    {
        return $this->negate();
    }

    /**
     * Configure form component based on settings.
     */
    protected function configureForm(): void
    {
        if (empty($this->options)) {
            return;
        }

        // Use Select component uniformly, supporting both single and multiple selection
        $labelResolver = $this->labelResolver();

        $select = Select::make($this->multiple ? 'values' : 'value')
            ->label($labelResolver)
            ->options($this->options)
            ->searchable($this->searchable)
            ->multiple($this->multiple)
            ->native(false)
            ->placeholder($this->multiple ? __('filament-dcat-filters::filament-dcat-filters.in.placeholder_multiple') : __('filament-dcat-filters::filament-dcat-filters.in.placeholder_single'))
            ->columnSpanFull();

        $this->applyInlineLabel($select, $labelResolver);

        $this->form([$select]);

        $this->configureQuery();
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            // Use relationship title column, custom column, or filter name
            $column = $this->relationshipTitleColumn ?? $this->resolveColumnName();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return $query;
                }

                // Relationship: wrap in whereHas
                if ($this->hasRelationship()) {
                    return $this->applyRelationshipWhereIn($query, $column, $values, $this->negate);
                }

                if ($this->negate) {
                    return $query->whereNotIn($column, $values);
                }

                return $query->whereIn($column, $values);
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return $query;
            }

            // Relationship: wrap in whereHas
            if ($this->hasRelationship()) {
                $operator = $this->negate ? '!=' : '=';

                return $this->applyRelationshipConstraint($query, $column, $operator, $value);
            }

            if ($this->negate) {
                return $query->where($column, '!=', $value);
            }

            return $query->where($column, $value);
        });

        $this->indicateUsing(function (array $data): array {
            $label = $this->resolveLabel();

            if ($this->multiple) {
                $values = $data['values'] ?? [];

                if (empty($values)) {
                    return [];
                }

                $labels = array_map(fn ($value) => $this->options[$value] ?? $value, $values);

                return [
                    Indicator::make("{$label}: ".implode(', ', $labels))
                        ->removeField('values'),
                ];
            }

            $value = $data['value'] ?? null;

            if ($value === null || $value === '') {
                return [];
            }

            $valueLabel = $this->options[$value] ?? $value;

            return [
                Indicator::make("{$label}: {$valueLabel}")
                    ->removeField('value'),
            ];
        });
    }
}
