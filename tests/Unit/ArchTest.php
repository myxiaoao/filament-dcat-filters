<?php

arch('it will not use debugging functions')
    ->expect(['dd', 'dump', 'ray', 'var_dump'])
    ->not->toBeUsed();

arch('globals should not be accessed')
    ->expect('Cooper\FilamentDcatFilters')
    ->not->toUse(['die', 'exit', 'eval']);

arch('concerns are traits')
    ->expect('Cooper\FilamentDcatFilters\Concerns')
    ->toBeTraits();

arch('facade extends base facade')
    ->expect('Cooper\FilamentDcatFilters\Facades\FilamentDcatFilters')
    ->toExtend('Illuminate\Support\Facades\Facade');

arch('filters should not depend on components')
    ->expect('Cooper\FilamentDcatFilters\Filters')
    ->not->toUse('Cooper\FilamentDcatFilters\Components');
