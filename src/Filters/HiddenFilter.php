<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasColumnName;
use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Concerns\HasOperator;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class HiddenFilter extends Filter
{
    use HasColumnName;
    use HasFilterState;
    use HasOperator;

    protected const ALLOWED_OPERATORS = ['=', '!=', '>', '>=', '<', '<=', 'like', 'not like'];

    protected mixed $defaultValue = null;

    protected function describeState(): FilterStateDescriptor
    {
        return FilterStateDescriptor::make()
            ->fields(['value'])
            ->type(StateType::Single)
            ->databaseSupport(['mysql', 'pgsql', 'sqlite']);
    }

    /**
     * Setup default configuration.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->columnSpan(1);

        // Configure hidden input
        $this->form([
            Hidden::make('value')
                ->default($this->defaultValue),
        ]);

        $this->configureQuery();
    }

    /**
     * Set the default value for this filter.
     */
    public function default(mixed $state = true): static
    {
        $this->defaultValue = $state;

        $this->form([
            Hidden::make('value')
                ->default($this->defaultValue),
        ]);

        return $this;
    }

    /**
     * Configure the query logic for this filter.
     */
    protected function configureQuery(): void
    {
        $this->query(function (Builder $query, array $data): Builder {
            $value = $data['value'] ?? $this->defaultValue;

            if ($value === null || $value === '') {
                return $query;
            }

            $column = $this->resolveColumnName();

            // Validate operator at query time as well
            if (! in_array($this->operator, self::ALLOWED_OPERATORS, true)) {
                return $query;
            }

            return $query->where($column, $this->operator, $value);
        });

        // Hidden filters don't show indicators
        $this->indicateUsing(fn () => []);
    }
}
