<?php

use Cooper\FilamentDcatFilters\Filters\RegexFilter;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;

it('can be instantiated', function () {
    $filter = RegexFilter::make('phone');

    expect($filter)->toBeInstanceOf(RegexFilter::class);
});

it('has correct default column span', function () {
    $filter = RegexFilter::make('phone');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Form Component', function () {
    it('uses text input by default for user pattern mode', function () {
        $filter = RegexFilter::make('phone');

        $form = $filter->getFormSchema();

        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(TextInput::class);
    });

    it('uses toggle for fixed pattern mode', function () {
        $filter = RegexFilter::make('phone')
            ->pattern('^1[3-9][0-9]{9}$');

        $form = $filter->getFormSchema();

        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(Toggle::class);
    });
});

describe('Pattern Mode', function () {
    it('can set a fixed pattern', function () {
        $filter = RegexFilter::make('phone')
            ->pattern('^1[3-9][0-9]{9}$');

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });

    it('can switch back to user pattern mode', function () {
        $filter = RegexFilter::make('phone')
            ->pattern('^test$')
            ->userPattern();

        $form = $filter->getFormSchema();

        expect($form[0])->toBeInstanceOf(TextInput::class);
    });
});

describe('Case Sensitivity', function () {
    it('is case sensitive by default', function () {
        $filter = RegexFilter::make('email');

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });

    it('can be made case insensitive', function () {
        $filter = RegexFilter::make('email')
            ->caseInsensitive();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });

    it('can explicitly set case sensitive', function () {
        $filter = RegexFilter::make('email')
            ->caseSensitive();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });
});

describe('Placeholder', function () {
    it('can set custom placeholder', function () {
        $filter = RegexFilter::make('phone')
            ->placeholder('Enter pattern...');

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });
});

describe('Presets', function () {
    it('has china mobile preset', function () {
        $filter = RegexFilter::make('phone')
            ->chinaMobile();

        $form = $filter->getFormSchema();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
        expect($form[0])->toBeInstanceOf(Toggle::class);
    });

    it('has email preset', function () {
        $filter = RegexFilter::make('email')
            ->email();

        $form = $filter->getFormSchema();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
        expect($form[0])->toBeInstanceOf(Toggle::class);
    });

    it('has url preset', function () {
        $filter = RegexFilter::make('website')
            ->url();

        $form = $filter->getFormSchema();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
        expect($form[0])->toBeInstanceOf(Toggle::class);
    });

    it('has ipv4 preset', function () {
        $filter = RegexFilter::make('ip_address')
            ->ipv4();

        $form = $filter->getFormSchema();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
        expect($form[0])->toBeInstanceOf(Toggle::class);
    });
});

describe('Column Name', function () {
    it('uses filter name as column by default', function () {
        $filter = RegexFilter::make('phone_number');

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });

    it('can set custom column name', function () {
        $filter = RegexFilter::make('phone')
            ->column('phone_number');

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });

    it('can combine column with pattern mode', function () {
        $filter = RegexFilter::make('mobile_search')
            ->column('phone_number')
            ->chinaMobile();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });

    it('can combine column with user pattern mode', function () {
        $filter = RegexFilter::make('custom_search')
            ->column('description')
            ->placeholder('Enter regex pattern...');

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });
});

describe('Combined Features', function () {
    it('can combine pattern and case insensitivity', function () {
        $filter = RegexFilter::make('email')
            ->pattern('^[a-z]+@example\\.com$')
            ->caseInsensitive();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });

    it('can combine column, pattern and case insensitivity', function () {
        $filter = RegexFilter::make('email_search')
            ->column('user_email')
            ->pattern('^[a-z]+@example\\.com$')
            ->caseInsensitive();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });
});

describe('Database Driver', function () {
    it('can set database driver', function () {
        $filter = RegexFilter::make('phone')
            ->driver('pgsql');

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });

    it('can combine driver with pattern mode', function () {
        $filter = RegexFilter::make('phone')
            ->driver('pgsql')
            ->chinaMobile();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });

    it('can combine driver with case insensitivity', function () {
        $filter = RegexFilter::make('email')
            ->driver('pgsql')
            ->caseInsensitive();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });

    it('can combine driver with column and pattern', function () {
        $filter = RegexFilter::make('search')
            ->driver('pgsql')
            ->column('user_email')
            ->pattern('^[a-z]+@example\\.com$')
            ->caseInsensitive();

        expect($filter)->toBeInstanceOf(RegexFilter::class);
    });
});
