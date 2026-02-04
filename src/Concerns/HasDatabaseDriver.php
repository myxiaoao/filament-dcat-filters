<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Illuminate\Database\Eloquent\Builder;

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
     * Check if the resolved driver is MySQL.
     */
    protected function isMysql(Builder $query): bool
    {
        return $this->resolveDriver($query) === 'mysql';
    }

    /**
     * Check if the resolved driver is SQLite.
     */
    protected function isSqlite(Builder $query): bool
    {
        return $this->resolveDriver($query) === 'sqlite';
    }
}
