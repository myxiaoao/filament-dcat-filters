<?php

use Cooper\FilamentDcatFilters\Filters\InputMaskFilter;
use Filament\Forms\Components\TextInput;

it('can be instantiated', function () {
    $filter = InputMaskFilter::make('phone');

    expect($filter)->toBeInstanceOf(InputMaskFilter::class);
});

it('has correct default column span', function () {
    $filter = InputMaskFilter::make('phone');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Form Component', function () {
    it('uses text input component', function () {
        $filter = InputMaskFilter::make('phone');

        $form = $filter->getFormSchema();

        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(TextInput::class);
    });
});

describe('Mask', function () {
    it('can set custom mask', function () {
        $filter = InputMaskFilter::make('phone')
            ->mask('(999) 999-9999');

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('can set placeholder', function () {
        $filter = InputMaskFilter::make('phone')
            ->placeholder('Enter phone number...');

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });
});

describe('Operators', function () {
    it('can use exact match', function () {
        $filter = InputMaskFilter::make('phone')
            ->exact();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('can use like match', function () {
        $filter = InputMaskFilter::make('phone')
            ->like();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('can set custom operator', function () {
        $filter = InputMaskFilter::make('phone')
            ->operator('=');

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });
});

describe('Strip Mask', function () {
    it('can enable strip mask', function () {
        $filter = InputMaskFilter::make('phone')
            ->stripMask();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('can disable strip mask', function () {
        $filter = InputMaskFilter::make('phone')
            ->stripMask(false);

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('can set custom strip pattern', function () {
        $filter = InputMaskFilter::make('phone')
            ->stripPattern('/[^0-9]/');

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });
});

describe('Presets', function () {
    it('has phone preset', function () {
        $filter = InputMaskFilter::make('phone')
            ->phone();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has phone preset with custom format', function () {
        $filter = InputMaskFilter::make('phone')
            ->phone('999-999-9999');

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has china phone preset', function () {
        $filter = InputMaskFilter::make('phone')
            ->chinaPhone();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has credit card preset', function () {
        $filter = InputMaskFilter::make('card_number')
            ->creditCard();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has date preset', function () {
        $filter = InputMaskFilter::make('birth_date')
            ->date();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has date preset with custom format', function () {
        $filter = InputMaskFilter::make('birth_date')
            ->date('99/99/9999');

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has time preset', function () {
        $filter = InputMaskFilter::make('start_time')
            ->time();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has time preset with custom format', function () {
        $filter = InputMaskFilter::make('start_time')
            ->time('99:99:99');

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has ip preset', function () {
        $filter = InputMaskFilter::make('ip_address')
            ->ip();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has zip code preset', function () {
        $filter = InputMaskFilter::make('postal_code')
            ->zipCode();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has zip code preset with custom format', function () {
        $filter = InputMaskFilter::make('postal_code')
            ->zipCode('99999-9999');

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has currency preset', function () {
        $filter = InputMaskFilter::make('amount')
            ->currency();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });

    it('has currency preset with custom prefix', function () {
        $filter = InputMaskFilter::make('amount')
            ->currency('¥');

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });
});

describe('Combined Features', function () {
    it('can combine mask, placeholder, and strip pattern', function () {
        $filter = InputMaskFilter::make('phone')
            ->mask('(999) 999-9999')
            ->placeholder('Phone number')
            ->stripPattern('/[^0-9]/')
            ->exact();

        expect($filter)->toBeInstanceOf(InputMaskFilter::class);
    });
});
