<?php

use Cooper\FilamentDcatFilters\Filters\ModalSelectFilter;

it('can be instantiated', function () {
    $filter = ModalSelectFilter::make('user_id');

    expect($filter)->toBeInstanceOf(ModalSelectFilter::class);
});

it('has correct default column span', function () {
    $filter = ModalSelectFilter::make('user_id');

    expect($filter->getColumnSpan())->toBe(1);
});

describe('Model Configuration', function () {
    it('can set model class', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->model('App\\Models\\User', 'name', 'id');

        expect($filter->getModelClass())->toBe('App\\Models\\User');
        expect($filter->getTitleColumn())->toBe('name');
        expect($filter->getKeyColumn())->toBe('id');
    });

    it('can set model with default columns', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->model('App\\Models\\User');

        expect($filter->getModelClass())->toBe('App\\Models\\User');
        expect($filter->getTitleColumn())->toBe('name');
        expect($filter->getKeyColumn())->toBe('id');
    });

    it('can set relationship', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->relationship('user', 'name', 'id');

        expect($filter)->toBeInstanceOf(ModalSelectFilter::class);
    });
});

describe('Dialog Configuration', function () {
    it('can set dialog title', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->dialogTitle('Select User');

        expect($filter->getDialogTitle())->toBe('Select User');
    });

    it('can use modalTitle alias', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->modalTitle('Select User');

        expect($filter->getDialogTitle())->toBe('Select User');
    });

    it('can set dialog width', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->dialogWidth('1200px');

        expect($filter->getDialogWidth())->toBe('1200px');
    });

    it('can use modalWidth alias', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->modalWidth('80%');

        expect($filter->getDialogWidth())->toBe('80%');
    });

    it('has default dialog width', function () {
        $filter = ModalSelectFilter::make('user_id');

        expect($filter->getDialogWidth())->toBe('900px');
    });
});

describe('Multiple Selection', function () {
    it('can enable multiple selection', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->multiple();

        expect($filter->isMultiple())->toBeTrue();
    });

    it('can disable multiple selection', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->multiple(false);

        expect($filter->isMultiple())->toBeFalse();
    });

    it('is single selection by default', function () {
        $filter = ModalSelectFilter::make('user_id');

        expect($filter->isMultiple())->toBeFalse();
    });
});

describe('Searchable Columns', function () {
    it('can set searchable columns', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->searchable(['name', 'email']);

        expect($filter)->toBeInstanceOf(ModalSelectFilter::class);
    });
});

describe('Display Columns', function () {
    it('can set display columns', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->displayColumns([
                'id' => 'ID',
                'name' => 'Name',
                'email' => 'Email',
            ]);

        expect($filter)->toBeInstanceOf(ModalSelectFilter::class);
    });
});

describe('Query Modification', function () {
    it('can set query modifier', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->modifyQueryUsing(fn ($query) => $query->where('active', true));

        expect($filter)->toBeInstanceOf(ModalSelectFilter::class);
    });
});

describe('Value Empty Check', function () {
    it('checks if value is empty correctly', function () {
        $filter = ModalSelectFilter::make('user_id');

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('isValueEmpty');
        $method->setAccessible(true);

        // Empty values
        expect($method->invoke($filter, null))->toBeTrue();
        expect($method->invoke($filter, ''))->toBeTrue();
        expect($method->invoke($filter, []))->toBeTrue();

        // Non-empty values
        expect($method->invoke($filter, 'value'))->toBeFalse();
        expect($method->invoke($filter, 0))->toBeFalse();
        expect($method->invoke($filter, [1, 2]))->toBeFalse();
    });
});

describe('Chained Configuration', function () {
    it('can chain all configuration methods', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->model('App\\Models\\User', 'name', 'id')
            ->dialogTitle('Select User')
            ->dialogWidth('1200px')
            ->multiple()
            ->searchable(['name', 'email'])
            ->displayColumns(['id' => 'ID', 'name' => 'Name'])
            ->modifyQueryUsing(fn ($query) => $query);

        expect($filter)->toBeInstanceOf(ModalSelectFilter::class);
        expect($filter->getModelClass())->toBe('App\\Models\\User');
        expect($filter->getDialogTitle())->toBe('Select User');
        expect($filter->getDialogWidth())->toBe('1200px');
        expect($filter->isMultiple())->toBeTrue();
    });
});
