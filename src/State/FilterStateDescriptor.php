<?php

namespace Cooper\FilamentDcatFilters\State;

use Closure;

class FilterStateDescriptor
{
    protected array $fields = [];

    protected StateType $type = StateType::Single;

    protected ?Closure $emptyCheck = null;

    protected array $capabilities = [];

    protected array $databaseSupport = ['mysql', 'pgsql', 'sqlite'];

    protected array $limitations = [];

    public static function make(): static
    {
        return new static;
    }

    public function fields(array $fields): static
    {
        $this->fields = $fields;

        return $this;
    }

    public function type(StateType $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function emptyWhen(Closure $callback): static
    {
        $this->emptyCheck = $callback;

        return $this;
    }

    public function capabilities(array $caps): static
    {
        $this->capabilities = array_values(array_filter($caps));

        return $this;
    }

    public function databaseSupport(array $drivers): static
    {
        $this->databaseSupport = $drivers;

        return $this;
    }

    public function limitations(array $notes): static
    {
        $this->limitations = $notes;

        return $this;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getType(): StateType
    {
        return $this->type;
    }

    /**
     * Check if the filter state is empty.
     *
     * If an explicit emptyWhen closure was provided, delegates to it.
     * Otherwise falls back to default: all declared fields are null, '', or [].
     * Filters with special empty semantics (e.g., BooleanFilter where '0' is valid,
     * NullFilter where 'null' is valid) must provide an explicit emptyWhen closure.
     */
    public function isEmpty(array $data): bool
    {
        if ($this->emptyCheck !== null) {
            return ($this->emptyCheck)($data);
        }

        foreach ($this->fields as $field) {
            $value = $data[$field] ?? null;

            if ($value !== null && $value !== '' && $value !== []) {
                return false;
            }
        }

        return true;
    }

    public function getCapabilities(): array
    {
        return $this->capabilities;
    }

    public function getDatabaseSupport(): array
    {
        return $this->databaseSupport;
    }

    public function getLimitations(): array
    {
        return $this->limitations;
    }
}
