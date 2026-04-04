<?php

namespace Cooper\FilamentDcatFilters\Components;

use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Support\Enums\Alignment;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Livewire\Component;

/**
 * ModalSelectTable Livewire Component
 *
 * Modal table selection component for ModalSelectFilter
 */
class ModalSelectTable extends Component implements HasActions, HasForms, HasTable
{
    use InteractsWithActions;
    use InteractsWithForms;
    use InteractsWithTable;

    public ?string $modelClass = null;

    public string $titleColumn = 'name';

    public string $keyColumn = 'id';

    public bool $multiple = false;

    public array $displayColumns = [];

    public array $searchColumns = [];

    public array $selected = [];

    public array $selectedSet = [];

    public string $filterKey = '';

    public int $renderKey = 0;

    public int $searchDebounce = 300;

    public int $minSearchLength = 1;

    /**
     * Mount the component.
     */
    public function mount(
        string $modelClass,
        string $titleColumn = 'name',
        string $keyColumn = 'id',
        bool $multiple = false,
        array $displayColumns = [],
        array $searchColumns = [],
        array $selected = [],
        string $filterKey = '',
        int $searchDebounce = 300,
        int $minSearchLength = 1
    ): void {
        $this->modelClass = $modelClass;
        $this->titleColumn = $titleColumn;
        $this->keyColumn = $keyColumn;
        $this->multiple = $multiple;
        $this->displayColumns = $displayColumns;
        $this->searchColumns = $searchColumns;
        $this->selected = $selected;
        $this->filterKey = $filterKey;
        $this->selectedSet = array_flip($this->selected);
        $this->searchDebounce = $searchDebounce;
        $this->minSearchLength = $minSearchLength;
    }

    /**
     * Configure the table.
     */
    public function table(Table $table): Table
    {
        $columns = $this->buildColumns();

        return $table
            ->query($this->getQuery())
            ->columns($columns)
            ->paginated(config('filament-dcat-filters.modal_select.pagination_options', [10, 25, 50]))
            ->defaultPaginationPageOption(config('filament-dcat-filters.modal_select.default_pagination', 10))
            ->searchable($this->searchColumns ? true : false)
            ->searchDebounce($this->searchDebounce)
            ->searchPlaceholder(__('filament-dcat-filters::filament-dcat-filters.like.placeholder'))
            ->striped()
            ->extremePaginationLinks()
            ->emptyStateHeading(__('filament-dcat-filters::filament-dcat-filters.modal_select.empty_state'))
            ->emptyStateDescription(__('filament-dcat-filters::filament-dcat-filters.modal_select.empty_state_description'));
    }

    /**
     * Get the query builder.
     */
    protected function getQuery(): Builder
    {
        if (! $this->modelClass) {
            throw new \RuntimeException('Model class is required.');
        }

        if (! class_exists($this->modelClass) || ! is_subclass_of($this->modelClass, Model::class)) {
            throw new \RuntimeException('Invalid model class.');
        }

        $allowedModels = config('filament-dcat-filters.allowed_models', []);
        if (! empty($allowedModels) && ! in_array($this->modelClass, $allowedModels, true)) {
            throw new \RuntimeException('Model not allowed.');
        }

        return $this->modelClass::query();
    }

    /**
     * Build table columns.
     */
    protected function buildColumns(): array
    {
        $columns = [];
        $keyColumn = $this->keyColumn;
        $selectAction = fn ($record) => $this->selectRow($record->{$keyColumn});

        // Add selection column (first column)
        $columns[] = ViewColumn::make('_select')
            ->label('')
            ->view('filament-dcat-filters::components.select-column')
            ->state(fn ($record) => [
                'key' => $record->{$this->keyColumn},
                'selected' => isset($this->selectedSet[$record->{$this->keyColumn}]),
                'multiple' => $this->multiple,
                'renderKey' => $this->renderKey,
            ])
            ->alignment(Alignment::Center)
            ->width('60px');

        // Use default columns if no display columns specified
        if (empty($this->displayColumns)) {
            $columns[] = TextColumn::make($this->keyColumn)
                ->label('ID')
                ->sortable()
                ->action($selectAction);

            $columns[] = TextColumn::make($this->titleColumn)
                ->label('Name')
                ->searchable()
                ->sortable()
                ->action($selectAction);
        } else {
            // Use specified display columns
            foreach ($this->displayColumns as $column => $label) {
                $col = TextColumn::make($column)
                    ->label($label)
                    ->sortable()
                    ->action($selectAction);

                // Add search functionality if column is in search columns
                if (in_array($column, $this->searchColumns)) {
                    $col->searchable();
                }

                $columns[] = $col;
            }
        }

        return $columns;
    }

    /**
     * Select a row.
     */
    public function selectRow(string|int $key): void
    {
        if ($this->multiple) {
            if (isset($this->selectedSet[$key])) {
                $this->selected = array_values(array_diff($this->selected, [$key]));
            } else {
                $this->selected[] = $key;
            }
        } else {
            $this->selected = [$key];
        }
        $this->selectedSet = array_flip($this->selected);
    }

    /**
     * Confirm selection.
     */
    public function confirm(): void
    {
        $this->dispatch('modal-select-confirmed',
            filterKey: $this->filterKey,
            selected: $this->selected,
            modelClass: $this->modelClass,
            titleColumn: $this->titleColumn,
            keyColumn: $this->keyColumn,
        );

        $this->dispatch('close-modal', id: 'modal-select-'.$this->filterKey);
    }

    /**
     * Cancel selection.
     */
    public function cancel(): void
    {
        $this->dispatch('close-modal', id: 'modal-select-'.$this->filterKey);
    }

    /**
     * Clear selection.
     */
    public function clearSelection(): void
    {
        $this->selected = [];
        $this->selectedSet = [];

        // Increment renderKey to force re-render all checkboxes
        $this->renderKey++;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('filament-dcat-filters::components.modal-select-table');
    }
}
