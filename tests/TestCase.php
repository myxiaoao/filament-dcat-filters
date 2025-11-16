<?php

namespace Cooper\FilamentDcatFilters\Tests;

use BladeUI\Heroicons\BladeHeroiconsServiceProvider;
use BladeUI\Icons\BladeIconsServiceProvider;
use Cooper\FilamentDcatFilters\FilamentDcatFiltersServiceProvider;
use Filament\FilamentServiceProvider;
use Filament\Forms\FormsServiceProvider;
use Filament\Support\SupportServiceProvider;
use Filament\Tables\TablesServiceProvider;
use Livewire\LivewireServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function getPackageProviders($app): array
    {
        return [
            BladeHeroiconsServiceProvider::class,
            BladeIconsServiceProvider::class,
            LivewireServiceProvider::class,
            FilamentServiceProvider::class,
            SupportServiceProvider::class,
            FormsServiceProvider::class,
            TablesServiceProvider::class,
            FilamentDcatFiltersServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Setup view paths
        $app['config']->set('view.paths', [
            __DIR__.'/../resources/views',
            resource_path('views'),
        ]);
    }
}
