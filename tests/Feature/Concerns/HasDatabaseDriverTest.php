<?php

use Cooper\FilamentDcatFilters\Concerns\HasDatabaseDriver;
use Illuminate\Database\Connection;
use Illuminate\Database\Eloquent\Builder;

// Create a concrete test class that uses the trait
class HasDatabaseDriverTestFilter
{
    use HasDatabaseDriver;

    public function getDriver(): ?string
    {
        return $this->databaseDriver;
    }

    public function testResolveDriver(Builder $query): string
    {
        return $this->resolveDriver($query);
    }

    public function testIsPostgres(Builder $query): bool
    {
        return $this->isPostgres($query);
    }
}

function createMockBuilder(string $driverName): Builder
{
    $connection = Mockery::mock(Connection::class);
    $connection->shouldReceive('getDriverName')->andReturn($driverName);

    $builder = Mockery::mock(Builder::class);
    $builder->shouldReceive('getConnection')->andReturn($connection);

    return $builder;
}

describe('Driver Configuration', function () {
    it('has null driver by default', function () {
        $filter = new HasDatabaseDriverTestFilter;

        expect($filter->getDriver())->toBeNull();
    });

    it('can set driver manually', function () {
        $filter = new HasDatabaseDriverTestFilter;
        $result = $filter->driver('pgsql');

        expect($filter->getDriver())->toBe('pgsql');
        expect($result)->toBe($filter);
    });
});

describe('Driver Resolution Priority', function () {
    it('uses filter-level driver when set', function () {
        $filter = (new HasDatabaseDriverTestFilter)->driver('pgsql');
        $builder = createMockBuilder('mysql');

        expect($filter->testResolveDriver($builder))->toBe('pgsql');
    });

    it('uses config driver when not auto', function () {
        config(['filament-dcat-filters.database.driver' => 'pgsql']);

        $filter = new HasDatabaseDriverTestFilter;
        $builder = createMockBuilder('mysql');

        expect($filter->testResolveDriver($builder))->toBe('pgsql');

        config(['filament-dcat-filters.database.driver' => 'auto']);
    });

    it('auto-detects from connection when config is auto', function () {
        config(['filament-dcat-filters.database.driver' => 'auto']);

        $filter = new HasDatabaseDriverTestFilter;
        $builder = createMockBuilder('sqlite');

        expect($filter->testResolveDriver($builder))->toBe('sqlite');
    });

    it('filter-level driver overrides config', function () {
        config(['filament-dcat-filters.database.driver' => 'mysql']);

        $filter = (new HasDatabaseDriverTestFilter)->driver('sqlite');
        $builder = createMockBuilder('pgsql');

        expect($filter->testResolveDriver($builder))->toBe('sqlite');

        config(['filament-dcat-filters.database.driver' => 'auto']);
    });
});

describe('Driver Detection Helpers', function () {
    it('detects PostgreSQL', function () {
        $filter = (new HasDatabaseDriverTestFilter)->driver('pgsql');
        $builder = createMockBuilder('mysql');

        expect($filter->testIsPostgres($builder))->toBeTrue();
    });

    it('does not detect PostgreSQL for other drivers', function () {
        $filter = (new HasDatabaseDriverTestFilter)->driver('mysql');
        $builder = createMockBuilder('pgsql');

        expect($filter->testIsPostgres($builder))->toBeFalse();
    });
});
