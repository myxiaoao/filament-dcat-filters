<?php

use Cooper\FilamentDcatFilters\State\FilterStateDescriptor;
use Cooper\FilamentDcatFilters\State\StateType;

describe('FilterStateDescriptor', function () {
    it('can be created with make()', function () {
        $descriptor = FilterStateDescriptor::make();
        expect($descriptor)->toBeInstanceOf(FilterStateDescriptor::class);
    });

    it('has default type of Single', function () {
        $descriptor = FilterStateDescriptor::make();
        expect($descriptor->getType())->toBe(StateType::Single);
    });

    it('has default database support for mysql, pgsql, sqlite', function () {
        $descriptor = FilterStateDescriptor::make();
        expect($descriptor->getDatabaseSupport())->toBe(['mysql', 'pgsql', 'sqlite']);
    });

    it('can set and get fields', function () {
        $descriptor = FilterStateDescriptor::make()->fields(['from', 'to']);
        expect($descriptor->getFields())->toBe(['from', 'to']);
    });

    it('can set and get type', function () {
        $descriptor = FilterStateDescriptor::make()->type(StateType::Range);
        expect($descriptor->getType())->toBe(StateType::Range);
    });

    it('can set and get capabilities', function () {
        $descriptor = FilterStateDescriptor::make()->capabilities(['relationship', 'indicator']);
        expect($descriptor->getCapabilities())->toBe(['relationship', 'indicator']);
    });

    it('filters null values from capabilities', function () {
        $descriptor = FilterStateDescriptor::make()->capabilities(['indicator', null, 'relationship', null]);
        expect($descriptor->getCapabilities())->toBe(['indicator', 'relationship']);
    });

    it('can set and get database support', function () {
        $descriptor = FilterStateDescriptor::make()->databaseSupport(['mysql', 'pgsql']);
        expect($descriptor->getDatabaseSupport())->toBe(['mysql', 'pgsql']);
    });

    it('can set and get limitations', function () {
        $descriptor = FilterStateDescriptor::make()->limitations(['No SQLite support']);
        expect($descriptor->getLimitations())->toBe(['No SQLite support']);
    });

    it('has empty limitations by default', function () {
        $descriptor = FilterStateDescriptor::make();
        expect($descriptor->getLimitations())->toBe([]);
    });
});

describe('isEmpty', function () {
    it('uses default check when no emptyWhen is set', function () {
        $descriptor = FilterStateDescriptor::make()->fields(['value']);

        expect($descriptor->isEmpty(['value' => '']))->toBeTrue();
        expect($descriptor->isEmpty(['value' => null]))->toBeTrue();
        expect($descriptor->isEmpty([]))->toBeTrue();
        expect($descriptor->isEmpty(['value' => 'hello']))->toBeFalse();
        expect($descriptor->isEmpty(['value' => 0]))->toBeFalse();
        expect($descriptor->isEmpty(['value' => '0']))->toBeFalse();
    });

    it('default check treats empty array as empty', function () {
        $descriptor = FilterStateDescriptor::make()->fields(['values']);

        expect($descriptor->isEmpty(['values' => []]))->toBeTrue();
        expect($descriptor->isEmpty(['values' => ['a']]))->toBeFalse();
    });

    it('default check requires all fields empty for Range type', function () {
        $descriptor = FilterStateDescriptor::make()->fields(['from', 'to']);

        expect($descriptor->isEmpty(['from' => '', 'to' => '']))->toBeTrue();
        expect($descriptor->isEmpty(['from' => '10', 'to' => '']))->toBeFalse();
        expect($descriptor->isEmpty(['from' => '', 'to' => '20']))->toBeFalse();
        expect($descriptor->isEmpty(['from' => '10', 'to' => '20']))->toBeFalse();
    });

    it('uses custom emptyWhen closure when set', function () {
        $descriptor = FilterStateDescriptor::make()
            ->fields(['value'])
            ->emptyWhen(fn (array $data) => ($data['value'] ?? '') === '' || $data['value'] === 'none');

        expect($descriptor->isEmpty(['value' => '']))->toBeTrue();
        expect($descriptor->isEmpty(['value' => 'none']))->toBeTrue();
        expect($descriptor->isEmpty(['value' => 'hello']))->toBeFalse();
    });
});

describe('StateType', function () {
    it('has all expected cases', function () {
        expect(StateType::cases())->toHaveCount(6);
    });

    it('has correct string values', function () {
        expect(StateType::Single->value)->toBe('single');
        expect(StateType::Multiple->value)->toBe('multiple');
        expect(StateType::Range->value)->toBe('range');
        expect(StateType::Toggle->value)->toBe('toggle');
        expect(StateType::Keyed->value)->toBe('keyed');
        expect(StateType::Composite->value)->toBe('composite');
    });
});

describe('Fluent API', function () {
    it('supports method chaining', function () {
        $descriptor = FilterStateDescriptor::make()
            ->fields(['value'])
            ->type(StateType::Single)
            ->emptyWhen(fn (array $data) => ($data['value'] ?? '') === '')
            ->capabilities(['indicator'])
            ->databaseSupport(['mysql'])
            ->limitations(['Test only']);

        expect($descriptor->getFields())->toBe(['value'])
            ->and($descriptor->getType())->toBe(StateType::Single)
            ->and($descriptor->getCapabilities())->toBe(['indicator'])
            ->and($descriptor->getDatabaseSupport())->toBe(['mysql'])
            ->and($descriptor->getLimitations())->toBe(['Test only']);
    });
});
