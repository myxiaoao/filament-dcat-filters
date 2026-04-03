<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;

trait HasFilterState
{
    abstract protected function describeState(): FilterStateDescriptor;

    public function getStateDescriptor(): FilterStateDescriptor
    {
        return $this->describeState();
    }

    public function isStateEmpty(array $data): bool
    {
        return $this->describeState()->isEmpty($data);
    }
}
