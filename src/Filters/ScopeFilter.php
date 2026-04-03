<?php

namespace Cooper\FilamentDcatFilters\Filters;

use Cooper\FilamentDcatFilters\Concerns\HasFilterState;
use Cooper\FilamentDcatFilters\Concerns\HasInlineLabel;
use Cooper\FilamentDcatFilters\Concerns\HasLabelResolver;
use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\Indicator;
use Illuminate\Database\Eloquent\Builder;

class ScopeFilter extends Filter
{
    use HasFilterState;
    use HasInlineLabel;
    use HasLabelResolver;

    protected array $scopes = [];

    protected ?string $defaultScope = null;

    protected string $displayStyle = 'select'; // 'radio' or 'select'

    protected array|int|null $columns = 4;

    protected bool $showBadge = true;

    protected function describeState(): FilterStateDescriptor
    {
        return FilterStateDescriptor::make()
            ->fields(['scope'])
            ->type(StateType::Keyed)
            ->emptyWhen(fn (array $data) => ($data['scope'] ?? '') === '')
            ->capabilities(['indicator'])
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
     * Define the available scopes for this filter.
     *
     * @param  array  $scopes  Array of scope definitions
     * @return static
     *
     * Example:
     * [
     *     'all' => ['label' => 'All', 'default' => true],
     *     'published' => [
     *         'label' => 'Published',
     *         'query' => fn($q) => $q->where('status', 'published'),
     *         'badge' => fn($count) => $count, // Optional: show count
     *         'icon' => 'heroicon-o-check', // Optional: icon
     *     ],
     * ]
     */
    public function scopes(array $scopes): static
    {
        $this->scopes = $scopes;

        // Find default scope
        foreach ($scopes as $key => $scope) {
            if (is_array($scope) && ($scope['default'] ?? false)) {
                $this->defaultScope = $key;
                break;
            }
        }

        // If no default scope is set, use the first one
        if (! $this->defaultScope && ! empty($scopes)) {
            $this->defaultScope = array_key_first($scopes);
        }

        $this->configureFormAndQuery();

        return $this;
    }

    /**
     * Set the display style for scope selection.
     *
     * @param  string  $style  'radio' for button-style, 'select' for dropdown
     */
    public function style(string $style): static
    {
        $this->displayStyle = $style;
        $this->configureFormAndQuery();

        return $this;
    }

    /**
     * Set the number of columns for radio button layout.
     */
    public function columns(array|int|null $columns = 2): static
    {
        $this->columns = $columns;
        $this->configureFormAndQuery();

        return $this;
    }

    /**
     * Enable or disable badge display.
     */
    public function badge(bool $show = true): static
    {
        $this->showBadge = $show;

        return $this;
    }

    /**
     * Configure form component and query logic.
     */
    protected function configureFormAndQuery(): void
    {
        if (empty($this->scopes)) {
            return;
        }

        // Prepare options for form component
        $options = [];
        foreach ($this->scopes as $key => $scope) {
            if (is_string($scope)) {
                // Simple string label
                $options[$key] = $scope;
            } elseif (is_array($scope)) {
                $options[$key] = $scope['label'] ?? ucfirst($key);
            }
        }

        // Create form component based on style
        $labelResolver = $this->labelResolver();

        $formComponent = match ($this->displayStyle) {
            'select' => $this->buildScopeSelectComponent($labelResolver, $options),
            default => Radio::make('scope')
                ->label($labelResolver)
                ->options($options)
                ->default($this->defaultScope)
                ->inline()
                ->columns($this->columns),
        };

        $this->form([$formComponent]);

        // Configure query logic
        $this->query(function (Builder $query, array $data): Builder {
            $selectedScope = $data['scope'] ?? $this->defaultScope;
            $scopeConfig = $this->scopes[$selectedScope] ?? null;

            if (is_array($scopeConfig) && isset($scopeConfig['query'])) {
                return $scopeConfig['query']($query);
            }

            return $query;
        });

        // Configure indicator
        $this->indicateUsing(function (array $data): array {
            $selectedScope = $data['scope'] ?? $this->defaultScope;

            // Don't show indicator for default scope
            if ($selectedScope === $this->defaultScope) {
                return [];
            }

            $scopeConfig = $this->scopes[$selectedScope] ?? null;

            if (! $scopeConfig) {
                return [];
            }

            $label = is_array($scopeConfig)
                ? ($scopeConfig['label'] ?? ucfirst($selectedScope))
                : $scopeConfig;

            return [
                Indicator::make($label)
                    ->removeField('scope'),
            ];
        });
    }

    /**
     * Build the select component with inline label support.
     */
    protected function buildScopeSelectComponent(\Closure $labelResolver, array $options): Select
    {
        $select = Select::make('scope')
            ->label($labelResolver)
            ->options($options)
            ->default($this->defaultScope)
            ->native(false)
            ->selectablePlaceholder(false)
            ->columnSpanFull();

        $this->applyInlineLabel($select, $labelResolver);

        return $select;
    }

    /**
     * Quick method to create common status scopes.
     */
    public static function forStatus(string $column = 'status', array $statuses = []): static
    {
        $scopes = ['all' => ['label' => __('filament-dcat-filters::filament-dcat-filters.scope.all'), 'default' => true]];

        foreach ($statuses as $key => $label) {
            $scopes[$key] = [
                'label' => $label,
                'query' => fn (Builder $query) => $query->where($column, $key),
            ];
        }

        return static::make('status_scope')
            ->scopes($scopes);
    }

    /**
     * Quick method to create date-based scopes.
     */
    public static function forDates(string $column = 'created_at'): static
    {
        return static::make('date_scope')
            ->scopes([
                'all' => ['label' => __('filament-dcat-filters::filament-dcat-filters.scope.all_time'), 'default' => true],
                'today' => [
                    'label' => __('filament-dcat-filters::filament-dcat-filters.scope.today'),
                    'query' => fn (Builder $query) => $query->whereDate($column, today()),
                ],
                'week' => [
                    'label' => __('filament-dcat-filters::filament-dcat-filters.scope.this_week'),
                    'query' => fn (Builder $query) => $query->whereBetween(
                        $column,
                        [now()->startOfWeek(), now()->endOfWeek()]
                    ),
                ],
                'month' => [
                    'label' => __('filament-dcat-filters::filament-dcat-filters.scope.this_month'),
                    'query' => fn (Builder $query) => $query->whereMonth($column, now()->month)
                        ->whereYear($column, now()->year),
                ],
                'year' => [
                    'label' => __('filament-dcat-filters::filament-dcat-filters.scope.this_year'),
                    'query' => fn (Builder $query) => $query->whereYear($column, now()->year),
                ],
            ]);
    }
}
