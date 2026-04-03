<?php

namespace Cooper\FilamentDcatFilters\Exceptions;

class UnsupportedDatabaseDriverException extends \RuntimeException
{
    public function __construct(string $filterClass, string $driver, array $supported)
    {
        parent::__construct(sprintf(
            '%s does not support the "%s" database driver. Supported drivers: %s.',
            class_basename($filterClass),
            $driver,
            implode(', ', $supported)
        ));
    }
}
