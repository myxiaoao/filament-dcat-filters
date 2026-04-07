<?php

namespace Cooper\FilamentDcatFilters\Concerns;

trait HasLabelResolver
{
    protected function resolveLabel(): string
    {
        /** @phpstan-ignore nullCoalesce.expr (Filament's getLabel() can return null at runtime) */
        return $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));
    }

    protected function labelResolver(): \Closure
    {
        return fn (): string => $this->resolveLabel();
    }
}
