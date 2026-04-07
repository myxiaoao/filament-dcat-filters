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

    it('can set relationship with model class', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->relationship('user', 'name', 'id', 'App\\Models\\User');

        expect($filter->getModelClass())->toBe('App\\Models\\User');
    });

    it('relationship does not override existing model class', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->model('App\\Models\\User', 'name', 'id')
            ->relationship('user');

        expect($filter->getModelClass())->toBe('App\\Models\\User');
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

        expect($filter->getDialogWidth())->toBe('5xl');
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

describe('Column Name', function () {
    it('uses filter name as column by default', function () {
        $filter = ModalSelectFilter::make('user_id');

        expect($filter)->toBeInstanceOf(ModalSelectFilter::class);
    });

    it('can set custom column name', function () {
        $filter = ModalSelectFilter::make('user_selector')
            ->column('user_id')
            ->model('App\\Models\\User', 'name', 'id');

        expect($filter)->toBeInstanceOf(ModalSelectFilter::class);
    });

    it('can combine column with other features', function () {
        $filter = ModalSelectFilter::make('author_picker')
            ->column('author_id')
            ->model('App\\Models\\User', 'name', 'id')
            ->multiple()
            ->dialogTitle('Select Authors');

        expect($filter)->toBeInstanceOf(ModalSelectFilter::class);
    });
});

describe('Indicator Fallback', function () {
    it('shows raw value when model class is not set', function () {
        $filter = ModalSelectFilter::make('user_id');

        $reflection = new ReflectionClass($filter);
        $indicateProp = $reflection->getProperty('indicateUsing');
        $indicateProp->setAccessible(true);
        $indicateFn = $indicateProp->getValue($filter);

        $bound = Closure::bind($indicateFn, $filter, get_class($filter));
        $result = $bound(['value' => '42']);

        expect($result)->not->toBeEmpty();
        expect($result[0]->getLabel())->toContain('42');
    });
});

describe('Search Configuration', function () {
    it('can set search debounce', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->searchDebounce(500);

        expect($filter->getSearchDebounce())->toBe(500);
    });

    it('can set minimum search length', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->minSearchLength(3);

        expect($filter->getMinSearchLength())->toBe(3);
    });

    it('defaults debounce to 300ms from config', function () {
        $filter = ModalSelectFilter::make('user_id');

        expect($filter->getSearchDebounce())->toBe(300);
    });

    it('reads defaults from remote_search config', function () {
        config([
            'filament-dcat-filters.remote_search.debounce' => 600,
            'filament-dcat-filters.remote_search.min_length' => 2,
        ]);

        $filter = ModalSelectFilter::make('user_id');

        expect($filter->getSearchDebounce())->toBe(600)
            ->and($filter->getMinSearchLength())->toBe(2);
    });

    it('passes searchDebounce and minSearchLength to viewData', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->model('App\\Models\\User', 'name', 'id')
            ->searchDebounce(400)
            ->minSearchLength(2);

        $schema = $filter->getFormSchema();
        expect($schema)->not->toBeEmpty();

        // Extract the ViewField's viewData array (contains closures pushed by viewData())
        $viewField = $schema[0];
        $ref = new ReflectionProperty($viewField, 'viewData');
        $ref->setAccessible(true);
        $viewDataEntries = $ref->getValue($viewField);

        // Find the closure entry and invoke it bound to the filter
        $resolved = [];
        foreach ($viewDataEntries as $entry) {
            if ($entry instanceof Closure) {
                $bound = Closure::bind($entry, $filter, get_class($filter));
                $resolved = array_merge($resolved, $bound() ?? []);
            } elseif (is_array($entry)) {
                $resolved = array_merge($resolved, $entry);
            }
        }

        expect($resolved)->toHaveKey('searchDebounce')
            ->and($resolved['searchDebounce'])->toBe(400)
            ->and($resolved)->toHaveKey('minSearchLength')
            ->and($resolved['minSearchLength'])->toBe(2);
    });
});

describe('Auto Confirm On Select', function () {
    it('is disabled by default', function () {
        $filter = ModalSelectFilter::make('user_id');

        expect($filter->shouldAutoConfirmOnSelect())->toBeFalse();
    });

    it('can be enabled for single select', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->autoConfirmOnSelect();

        expect($filter->shouldAutoConfirmOnSelect())->toBeTrue();
    });

    it('has no effect when multiple is enabled', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->multiple()
            ->autoConfirmOnSelect();

        expect($filter->shouldAutoConfirmOnSelect())->toBeFalse();
    });

    it('can be chained with other configuration', function () {
        $filter = ModalSelectFilter::make('user_id')
            ->model('App\\Models\\User', 'name', 'id')
            ->autoConfirmOnSelect()
            ->dialogTitle('Quick Select');

        expect($filter->shouldAutoConfirmOnSelect())->toBeTrue();
        expect($filter->getDialogTitle())->toBe('Quick Select');
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
