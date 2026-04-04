<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Shared properties and helpers for filters that query a model
 * to resolve display labels (SelectTableFilter, ModalSelectFilter, MorphRelationFilter).
 */
trait HasModelOptions
{
    protected ?string $modelClass = null;

    protected ?string $titleColumn = 'name';

    protected string $keyColumn = 'id';

    protected bool $multiple = false;

    public function getModelClass(): ?string
    {
        return $this->modelClass;
    }

    public function getTitleColumn(): ?string
    {
        return $this->titleColumn;
    }

    public function getKeyColumn(): string
    {
        return $this->keyColumn;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    /**
     * Resolve display name(s) from the model for indicator display.
     *
     * @param  mixed  $value  Single value or array of values
     * @return string|null Resolved display string, or null if model unavailable
     */
    protected function resolveModelDisplayName(mixed $value): ?string
    {
        $model = $this->modelClass;

        if (! $model || ! class_exists($model) || ! is_subclass_of($model, Model::class)) {
            return null;
        }

        try {
            if (is_array($value)) {
                if (empty($value)) {
                    return null;
                }

                return $model::query()
                    ->select([$this->keyColumn, $this->titleColumn ?? 'name'])
                    ->whereIn($this->keyColumn, $value)
                    ->pluck($this->titleColumn ?? 'name')
                    ->implode(', ');
            }

            $record = $model::query()
                ->select([$this->keyColumn, $this->titleColumn ?? 'name'])
                ->where($this->keyColumn, $value)
                ->first();

            return $record ? $record->{$this->titleColumn ?? 'name'} : null;
        } catch (\Exception) {
            return null;
        }
    }
}
