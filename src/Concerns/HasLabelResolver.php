<?php

namespace Cooper\FilamentDcatFilters\Concerns;

trait HasLabelResolver
{
    protected function resolveLabel(): string
    {
        return $this->getLabel() ?? ucfirst(str_replace('_', ' ', $this->getName()));
    }

    protected function labelResolver(): \Closure
    {
        return fn (): string => $this->resolveLabel();
    }
}
