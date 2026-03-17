<?php

use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->filesystem = app(Filesystem::class);
    $this->outputPath = app_path('Filament/Filters');
});

afterEach(function () {
    if ($this->filesystem->isDirectory($this->outputPath)) {
        $this->filesystem->deleteDirectory($this->outputPath);
    }
});

it('creates a basic filter', function () {
    $this->artisan('make:dcat-filter', ['name' => 'TestStatus'])
        ->assertSuccessful();

    expect($this->outputPath.'/TestStatusFilter.php')->toBeFile();
});

it('appends Filter suffix automatically', function () {
    $this->artisan('make:dcat-filter', ['name' => 'Category'])
        ->assertSuccessful();

    expect($this->outputPath.'/CategoryFilter.php')->toBeFile();
});

it('does not duplicate Filter suffix', function () {
    $this->artisan('make:dcat-filter', ['name' => 'CategoryFilter'])
        ->assertSuccessful();

    expect($this->outputPath.'/CategoryFilter.php')->toBeFile();
});

it('supports all filter types', function (string $type) {
    $this->artisan('make:dcat-filter', ['name' => 'TypeTest'.ucfirst($type), '--type' => $type])
        ->assertSuccessful();
})->with([
    'basic', 'like', 'in', 'comparison', 'boolean', 'null', 'enum', 'range', 'regex', 'fulltext', 'json',
    'between', 'scope', 'date-component', 'select-table', 'modal-select', 'hidden',
    'relative-date', 'cascading-select', 'find-in-set', 'input-mask', 'geo-location', 'filter-group',
]);

it('rejects invalid filter type', function () {
    $this->artisan('make:dcat-filter', ['name' => 'Test', '--type' => 'invalid'])
        ->assertFailed();
});

it('does not overwrite without force flag', function () {
    $this->artisan('make:dcat-filter', ['name' => 'Duplicate'])->assertSuccessful();
    $this->artisan('make:dcat-filter', ['name' => 'Duplicate'])->assertFailed();
});

it('overwrites with force flag', function () {
    $this->artisan('make:dcat-filter', ['name' => 'Overwrite'])->assertSuccessful();
    $this->artisan('make:dcat-filter', ['name' => 'Overwrite', '--force' => true])->assertSuccessful();
});

it('generates correct parent class for like type', function () {
    $this->artisan('make:dcat-filter', ['name' => 'PriceLike', '--type' => 'like'])->assertSuccessful();

    $content = file_get_contents($this->outputPath.'/PriceLikeFilter.php');
    expect($content)
        ->toContain('use Cooper\FilamentDcatFilters\Filters\LikeFilter;')
        ->toContain('extends LikeFilter');
});

it('generates correct parent class for basic type', function () {
    $this->artisan('make:dcat-filter', ['name' => 'BasicTest', '--type' => 'basic'])->assertSuccessful();

    $content = file_get_contents($this->outputPath.'/BasicTestFilter.php');
    expect($content)
        ->toContain('use Filament\Tables\Filters\Filter;')
        ->toContain('extends Filter');
});

it('generates correct parent class for each new type', function (string $type, string $expectedClass, string $expectedShortName) {
    $name = 'Gen'.str_replace('-', '', ucwords($type, '-'));
    $this->artisan('make:dcat-filter', ['name' => $name, '--type' => $type])->assertSuccessful();

    $content = file_get_contents($this->outputPath.'/'.$name.'Filter.php');
    expect($content)
        ->toContain("use {$expectedClass};")
        ->toContain("extends {$expectedShortName}");
})->with([
    'between' => ['between', 'Cooper\FilamentDcatFilters\Filters\BetweenFilter', 'BetweenFilter'],
    'scope' => ['scope', 'Cooper\FilamentDcatFilters\Filters\ScopeFilter', 'ScopeFilter'],
    'date-component' => ['date-component', 'Cooper\FilamentDcatFilters\Filters\DateComponentFilter', 'DateComponentFilter'],
    'select-table' => ['select-table', 'Cooper\FilamentDcatFilters\Filters\SelectTableFilter', 'SelectTableFilter'],
    'modal-select' => ['modal-select', 'Cooper\FilamentDcatFilters\Filters\ModalSelectFilter', 'ModalSelectFilter'],
    'hidden' => ['hidden', 'Cooper\FilamentDcatFilters\Filters\HiddenFilter', 'HiddenFilter'],
    'relative-date' => ['relative-date', 'Cooper\FilamentDcatFilters\Filters\RelativeDateFilter', 'RelativeDateFilter'],
    'cascading-select' => ['cascading-select', 'Cooper\FilamentDcatFilters\Filters\CascadingSelectFilter', 'CascadingSelectFilter'],
    'find-in-set' => ['find-in-set', 'Cooper\FilamentDcatFilters\Filters\FindInSetFilter', 'FindInSetFilter'],
    'input-mask' => ['input-mask', 'Cooper\FilamentDcatFilters\Filters\InputMaskFilter', 'InputMaskFilter'],
    'geo-location' => ['geo-location', 'Cooper\FilamentDcatFilters\Filters\GeoLocationFilter', 'GeoLocationFilter'],
    'filter-group' => ['filter-group', 'Cooper\FilamentDcatFilters\Filters\FilterGroup', 'FilterGroup'],
]);
