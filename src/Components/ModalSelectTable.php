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

    public string $filterKey = '';

    public int $renderKey = 0;

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
        string $filterKey = ''
    ): void {
        $this->modelClass = $modelClass;
        $this->titleColumn = $titleColumn;
        $this->keyColumn = $keyColumn;
        $this->multiple = $multiple;
        $this->displayColumns = $displayColumns;
        $this->searchColumns = $searchColumns;
        $this->selected = $selected;
        $this->filterKey = $filterKey;
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
            ->paginated([10, 25, 50])
            ->defaultPaginationPageOption(10)
            ->searchable($this->searchColumns ? true : false)
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

        return $this->modelClass::query();
    }

    /**
     * Build table columns.
     */
    protected function buildColumns(): array
    {
        $columns = [];

        // Add selection column (first column)
        $columns[] = ViewColumn::make('_select')
            ->label('')
            ->view('filament-dcat-filters::components.select-column')
            ->state(fn ($record) => [
                'key' => $record->{$this->keyColumn},
                'selected' => in_array($record->{$this->keyColumn}, $this->selected),
                'multiple' => $this->multiple,
                'renderKey' => $this->renderKey,
            ])
            ->alignment(Alignment::Center)
            ->width('60px');

        // Use default columns if no display columns specified
        if (empty($this->displayColumns)) {
            $columns[] = TextColumn::make($this->keyColumn)
                ->label('ID')
                ->sortable();

            $columns[] = TextColumn::make($this->titleColumn)
                ->label('Name')
                ->searchable()
                ->sortable();
        } else {
            // Use specified display columns
            foreach ($this->displayColumns as $column => $label) {
                $col = TextColumn::make($column)
                    ->label($label)
                    ->sortable();

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
            if (in_array($key, $this->selected)) {
                $this->selected = array_values(array_diff($this->selected, [$key]));
            } else {
                $this->selected[] = $key;
            }
        } else {
            $this->selected = [$key];
        }
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

        // Increment renderKey to force re-render all checkboxes
        $this->renderKey++;
    }

    /**
     * Render the component.
     */
    public function render(): View
    {
        return view('filament-dcat-filters::components.modal-select-table')
            ->layout('');
    }
}
