<?php

use Cooper\FilamentDcatFilters\Http\Controllers\ModalSelectController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web', 'throttle:60,1'])
    ->prefix('filament-dcat-filters')
    ->name('filament-dcat-filters.')
    ->group(function () {
        Route::post('/fetch-labels', [ModalSelectController::class, 'fetchLabels'])
            ->name('fetch-labels');
    });
