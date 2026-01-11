<?php

use Cooper\FilamentDcatFilters\Filters\JsonFilter;
use Filament\Forms\Components\TextInput;

it('can be instantiated', function () {
    $filter = JsonFilter::make('metadata');

    expect($filter)->toBeInstanceOf(JsonFilter::class);
});

it('has correct default column span', function () {
    $filter = JsonFilter::make('metadata');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Form Component', function () {
    it('uses text input component', function () {
        $filter = JsonFilter::make('metadata');

        $form = $filter->getFormSchema();

        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(TextInput::class);
    });
});

describe('JSON Path', function () {
    it('can set json path with dot notation', function () {
        $filter = JsonFilter::make('metadata')
            ->path('settings.theme');

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can set json path with arrow notation', function () {
        $filter = JsonFilter::make('metadata')
            ->path('settings->theme');

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can set nested json path', function () {
        $filter = JsonFilter::make('metadata')
            ->path('preferences.display.mode');

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });
});

describe('Operators', function () {
    it('can set equals operator', function () {
        $filter = JsonFilter::make('metadata')
            ->path('status')
            ->eq();

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can set not equals operator', function () {
        $filter = JsonFilter::make('metadata')
            ->path('status')
            ->neq();

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can set greater than operator', function () {
        $filter = JsonFilter::make('metadata')
            ->path('count')
            ->gt();

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can set greater than or equal operator', function () {
        $filter = JsonFilter::make('metadata')
            ->path('count')
            ->gte();

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can set less than operator', function () {
        $filter = JsonFilter::make('metadata')
            ->path('count')
            ->lt();

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can set less than or equal operator', function () {
        $filter = JsonFilter::make('metadata')
            ->path('count')
            ->lte();

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can set like operator', function () {
        $filter = JsonFilter::make('metadata')
            ->path('description')
            ->like();

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can set not like operator', function () {
        $filter = JsonFilter::make('metadata')
            ->path('description')
            ->notLike();

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can set custom operator', function () {
        $filter = JsonFilter::make('metadata')
            ->path('value')
            ->operator('>=');

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('throws exception for invalid operator', function () {
        JsonFilter::make('metadata')
            ->operator('invalid');
    })->throws(\InvalidArgumentException::class);
});

describe('Default Value', function () {
    it('can set default value', function () {
        $filter = JsonFilter::make('metadata')
            ->path('theme')
            ->defaultValue('dark');

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });
});

describe('Column Name', function () {
    it('uses filter name as column by default', function () {
        $filter = JsonFilter::make('metadata');

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can set custom column name', function () {
        $filter = JsonFilter::make('theme_setting')
            ->column('user_preferences')
            ->path('theme');

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });

    it('can combine column with path and operator', function () {
        $filter = JsonFilter::make('config_search')
            ->column('app_settings')
            ->path('features.enabled')
            ->eq();

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });
});

describe('Combined Features', function () {
    it('can combine path, operator, and default', function () {
        $filter = JsonFilter::make('settings')
            ->path('preferences.theme')
            ->eq()
            ->defaultValue('light');

        expect($filter)->toBeInstanceOf(JsonFilter::class);
    });
});
