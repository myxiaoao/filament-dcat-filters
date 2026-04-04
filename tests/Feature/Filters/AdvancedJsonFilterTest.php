<?php

use Cooper\FilamentDcatFilters\Filters\AdvancedJsonFilter;
use Cooper\FilamentDcatFilters\State\StateType;
use Filament\Forms\Components\Toggle;

it('can be instantiated', function () {
    $filter = AdvancedJsonFilter::make('tags');
    expect($filter)->toBeInstanceOf(AdvancedJsonFilter::class);
});

describe('Modes', function () {
    it('defaults to arrayContains mode', function () {
        $filter = AdvancedJsonFilter::make('tags')->arrayContains();
        expect($filter->getMode())->toBe('arrayContains');
    });

    it('can set pathExists mode', function () {
        $filter = AdvancedJsonFilter::make('metadata')->pathExists('settings.theme');
        expect($filter->getMode())->toBe('pathExists');
    });

    it('can set hasKey mode', function () {
        $filter = AdvancedJsonFilter::make('config')->hasKey('notifications');
        expect($filter->getMode())->toBe('hasKey');
    });

    it('throws for invalid JSON path', function () {
        expect(fn () => AdvancedJsonFilter::make('meta')->pathExists('invalid path!'))
            ->toThrow(InvalidArgumentException::class);
    });

    it('throws for invalid JSON key', function () {
        expect(fn () => AdvancedJsonFilter::make('meta')->hasKey('invalid key!'))
            ->toThrow(InvalidArgumentException::class);
    });
});

describe('Form', function () {
    it('uses toggle for pathExists mode', function () {
        $filter = AdvancedJsonFilter::make('meta')->pathExists('settings');
        $form = $filter->getFormSchema();
        expect($form[0])->toBeInstanceOf(Toggle::class);
    });

    it('uses toggle for hasKey mode', function () {
        $filter = AdvancedJsonFilter::make('config')->hasKey('key');
        $form = $filter->getFormSchema();
        expect($form[0])->toBeInstanceOf(Toggle::class);
    });

    it('can set options for arrayContains', function () {
        $filter = AdvancedJsonFilter::make('tags')
            ->arrayContains()
            ->options(['php' => 'PHP', 'js' => 'JavaScript']);
        expect($filter)->toBeInstanceOf(AdvancedJsonFilter::class);
    });

    it('can enable multiple selection', function () {
        $filter = AdvancedJsonFilter::make('tags')
            ->arrayContains()
            ->options(['php' => 'PHP'])
            ->multiple();
        expect($filter)->toBeInstanceOf(AdvancedJsonFilter::class);
    });
});

describe('State Descriptor', function () {
    it('returns Single for arrayContains text mode', function () {
        $filter = AdvancedJsonFilter::make('tags')->arrayContains();
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Single);
        expect($d->getFields())->toBe(['value']);
    });

    it('returns Multiple for arrayContains multiple mode', function () {
        $filter = AdvancedJsonFilter::make('tags')->arrayContains()->multiple();
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Multiple);
        expect($d->getFields())->toBe(['values']);
    });

    it('returns Toggle for pathExists mode', function () {
        $filter = AdvancedJsonFilter::make('meta')->pathExists('settings');
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Toggle);
        expect($d->getFields())->toBe(['enabled']);
    });

    it('returns Toggle for hasKey mode', function () {
        $filter = AdvancedJsonFilter::make('config')->hasKey('key');
        $d = $filter->getStateDescriptor();
        expect($d->getType())->toBe(StateType::Toggle);
    });

    it('declares mysql and pgsql support', function () {
        $filter = AdvancedJsonFilter::make('tags')->arrayContains();
        $d = $filter->getStateDescriptor();
        expect($d->getDatabaseSupport())->toBe(['mysql', 'pgsql']);
    });
});
