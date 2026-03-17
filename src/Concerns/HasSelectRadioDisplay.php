<?php

namespace Cooper\FilamentDcatFilters\Concerns;

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
}
