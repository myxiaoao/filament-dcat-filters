<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Cooper\FilamentDcatFilters\Exceptions\UnsupportedDatabaseDriverException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;

trait HasDatabaseDriver
{
    protected ?string $databaseDriver = null;

    /**
     * Manually set the database driver for this filter.
     */
    public function driver(string $driver): static
    {
        $this->databaseDriver = $driver;

        return $this;
    }

    /**
     * Resolve the database driver with priority:
     * 1. Filter-level override ($this->databaseDriver)
     * 2. Package config (filament-dcat-filters.database.driver)
     * 3. Auto-detect from query connection
     */
    protected function resolveDriver(Builder $query): string
    {
        if ($this->databaseDriver !== null) {
            return $this->databaseDriver;
        }

        $configDriver = config('filament-dcat-filters.database.driver', 'auto');

        if ($configDriver !== 'auto') {
            return $configDriver;
        }

        return $query->getConnection()->getDriverName();
    }

    /**
     * Check if the resolved driver is PostgreSQL.
     */
    protected function isPostgres(Builder $query): bool
    {
        return $this->resolveDriver($query) === 'pgsql';
    }

    /**
     * Assert the current database driver is supported by this filter.
     *
     * Uses the filter's state descriptor to check driver compatibility.
     * - Supported drivers: pass silently
     * - Degraded drivers: log warning, continue
     * - Unsupported drivers: throw exception (fail-fast)
     */
    protected function assertDriverSupported(Builder $query): void
    {
        /** @phpstan-ignore function.alreadyNarrowedType (defensive check — trait may be used without HasFilterState) */
        if (! method_exists($this, 'getStateDescriptor')) {
            return;
        }

        $driver = $this->resolveDriver($query);
        $descriptor = $this->getStateDescriptor();
        $supported = $descriptor->getDatabaseSupport();
        $degraded = $descriptor->getDegradedSupport();

        if (in_array($driver, $supported)) {
            return;
        }

        if (in_array($driver, $degraded)) {
            Log::warning(sprintf(
                '%s is running in degraded mode on "%s" driver. Some features may not work as expected.',
                class_basename(static::class),
                $driver
            ));

            return;
        }

        throw new UnsupportedDatabaseDriverException(
            static::class,
            $driver,
            array_merge($supported, $degraded)
        );
    }
}
