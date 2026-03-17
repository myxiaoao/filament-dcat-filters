<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;

trait HasSelectRadioDisplay
{
    protected string $displayStyle = 'select';

    protected array|int|null $radioColumns = 3;

    /**
     * Use radio buttons display style.
     */
    public function radio(): static
    {
        $this->displayStyle = 'radio';
        $this->configureForm();

        return $this;
    }

    /**
     * Use select dropdown display style (default).
     */
    public function select(): static
    {
        $this->displayStyle = 'select';
        $this->configureForm();

        return $this;
    }

    /**
     * Set the number of columns for radio button layout.
     */
    public function columns(array|int|null $columns = 3): static
    {
        $this->radioColumns = $columns;
        $this->configureForm();

        return $this;
    }

    /**
     * Build a select or radio component based on display style.
     */
    protected function buildFormComponent(string $fieldName, \Closure $labelResolver, array $options, string $placeholder): Select|Radio
    {
        if ($this->displayStyle === 'radio') {
            return Radio::make($fieldName)
                ->label($labelResolver)
                ->options($options)
                ->default('')
                ->inline()
                ->columns($this->radioColumns)
                ->columnSpanFull();
        }

        $select = Select::make($fieldName)
            ->label($labelResolver)
            ->options($options)
            ->default('')
            ->native(false)
            ->placeholder($placeholder)
            ->columnSpanFull();

        if (method_exists($this, 'applyInlineLabel')) {
            $this->applyInlineLabel($select, $labelResolver);
        }

        return $select;
    }
}
