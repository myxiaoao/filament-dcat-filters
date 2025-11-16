<?php

namespace Cooper\FilamentDcatFilters;

use Livewire\Livewire;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentDcatFiltersServiceProvider extends PackageServiceProvider
{
    /**
     * Configure the package.
     */
    public function configurePackage(Package $package): void
    {
        $package
            ->name('filament-dcat-filters')
            ->hasConfigFile()
            ->hasViews()
            ->hasTranslations()
            ->hasRoutes(['web']);
    }

    /**
     * Package has been booted.
     */
    public function packageBooted(): void
    {
        // Register Livewire components
        $this->registerLivewireComponents();
    }

    /**
     * Package has been registered.
     */
    public function packageRegistered(): void
    {
        // Register the main class as a singleton
        $this->app->singleton(FilamentDcatFilters::class, function () {
            return new FilamentDcatFilters;
        });
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        if (class_exists(Livewire::class)) {
            Livewire::component(
                'cooper.filament-dcat-filters.modal-select-table',
                Components\ModalSelectTable::class
            );
        }
    }
}
