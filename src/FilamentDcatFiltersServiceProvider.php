<?php

namespace Cooper\FilamentDcatFilters;

use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Enums\FiltersResetActionPosition;
use Filament\Tables\Table;
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
        $this->registerLivewireComponents();
        $this->configureTable();

        if ($this->app->runningInConsole()) {
            $this->commands([
                Commands\MakeDcatFilterCommand::class,
            ]);
        }
    }

    /**
     * Package has been registered.
     */
    public function packageRegistered(): void
    {
        $this->app->singleton(FilamentDcatFilters::class, function () {
            return new FilamentDcatFilters;
        });
    }

    /**
     * Register Livewire components.
     */
    protected function registerLivewireComponents(): void
    {
        Livewire::component(
            'cooper.filament-dcat-filters.modal-select-table',
            Components\ModalSelectTable::class
        );
    }

    /**
     * Apply global table configuration based on package config.
     */
    protected function configureTable(): void
    {
        Table::configureUsing(function (Table $table): void {
            if (config('filament-dcat-filters.table.filters_above_content', true)) {
                $table->filtersLayout(FiltersLayout::AboveContent);
            }

            if (config('filament-dcat-filters.table.reset_action_in_footer', true)) {
                $table->filtersResetActionPosition(
                    FiltersResetActionPosition::Footer
                );
            }

            $columns = config('filament-dcat-filters.table.filters_form_columns');

            if ($columns !== null) {
                $table->filtersFormColumns($columns);
            }
        });
    }
}
