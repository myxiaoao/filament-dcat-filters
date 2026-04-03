<?php

use Cooper\FilamentDcatFilters\Filters\GeoLocationFilter;
use Filament\Schemas\Components\Grid;

it('can be instantiated', function () {
    $filter = GeoLocationFilter::make('location');

    expect($filter)->toBeInstanceOf(GeoLocationFilter::class);
});

it('has correct default column span', function () {
    $filter = GeoLocationFilter::make('location');

    expect($filter->getColumnSpan())->toBe(2);
});

describe('Columns', function () {
    it('has default latitude column', function () {
        $filter = GeoLocationFilter::make('location');

        expect($filter->getLatitudeColumn())->toBe('latitude');
    });

    it('has default longitude column', function () {
        $filter = GeoLocationFilter::make('location');

        expect($filter->getLongitudeColumn())->toBe('longitude');
    });

    it('can set latitude column', function () {
        $filter = GeoLocationFilter::make('location')
            ->latitude('lat');

        expect($filter->getLatitudeColumn())->toBe('lat');
    });

    it('can set longitude column', function () {
        $filter = GeoLocationFilter::make('location')
            ->longitude('lng');

        expect($filter->getLongitudeColumn())->toBe('lng');
    });

    it('can set both columns at once', function () {
        $filter = GeoLocationFilter::make('location')
            ->coordinates('lat', 'lng');

        expect($filter->getLatitudeColumn())->toBe('lat');
        expect($filter->getLongitudeColumn())->toBe('lng');
    });
});

describe('Radius', function () {
    it('has default radius of 10', function () {
        $filter = GeoLocationFilter::make('location');

        expect($filter->getDefaultRadius())->toBe(10.0);
    });

    it('can set radius', function () {
        $filter = GeoLocationFilter::make('location')
            ->radius(25);

        expect($filter->getDefaultRadius())->toBe(25.0);
    });

    it('can set radius with unit', function () {
        $filter = GeoLocationFilter::make('location')
            ->radius(5, 'mi');

        expect($filter->getDefaultRadius())->toBe(5.0);
        expect($filter->getUnit())->toBe('mi');
    });
});

describe('Units', function () {
    it('defaults to kilometers', function () {
        $filter = GeoLocationFilter::make('location');

        expect($filter->getUnit())->toBe('km');
    });

    it('can use kilometers', function () {
        $filter = GeoLocationFilter::make('location')
            ->kilometers();

        expect($filter->getUnit())->toBe('km');
    });

    it('can use miles', function () {
        $filter = GeoLocationFilter::make('location')
            ->miles();

        expect($filter->getUnit())->toBe('mi');
    });

    it('can use meters', function () {
        $filter = GeoLocationFilter::make('location')
            ->meters();

        expect($filter->getUnit())->toBe('m');
    });
});

describe('Center Point', function () {
    it('can set center point', function () {
        $filter = GeoLocationFilter::make('location')
            ->center(40.7128, -74.0060);

        expect($filter)->toBeInstanceOf(GeoLocationFilter::class);
    });
});

describe('Unit Factors', function () {
    it('has correct UNIT_FACTORS constant', function () {
        $reflection = new ReflectionClass(GeoLocationFilter::class);
        $constant = $reflection->getConstant('UNIT_FACTORS');

        expect($constant)->toBe([
            'km' => 1,
            'mi' => 1.60934,
            'm' => 0.001,
        ]);
    });

    it('has correct EARTH_RADIUS_KM constant', function () {
        $reflection = new ReflectionClass(GeoLocationFilter::class);
        $constant = $reflection->getConstant('EARTH_RADIUS_KM');

        expect($constant)->toBe(6371);
    });
});

describe('Unit Label', function () {
    it('returns km label for kilometers', function () {
        $filter = GeoLocationFilter::make('location')->kilometers();

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('getUnitLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBeString();
    });

    it('returns mi label for miles', function () {
        $filter = GeoLocationFilter::make('location')->miles();

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('getUnitLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBeString();
    });

    it('returns m label for meters', function () {
        $filter = GeoLocationFilter::make('location')->meters();

        $reflection = new ReflectionClass($filter);
        $method = $reflection->getMethod('getUnitLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBeString();
    });

    it('returns raw unit string for unknown unit', function () {
        $filter = GeoLocationFilter::make('location');

        $reflection = new ReflectionClass($filter);
        $unitProperty = $reflection->getProperty('unit');
        $unitProperty->setAccessible(true);
        $unitProperty->setValue($filter, 'ft');

        $method = $reflection->getMethod('getUnitLabel');
        $method->setAccessible(true);

        expect($method->invoke($filter))->toBe('ft');
    });
});

describe('Form Schema', function () {
    it('generates form with three inputs in a grid', function () {
        $filter = GeoLocationFilter::make('location');
        $form = $filter->getFormSchema();

        expect($form)->toHaveCount(1);
        expect($form[0])->toBeInstanceOf(Grid::class);
    });
});

describe('Combined Features', function () {
    it('can combine all settings', function () {
        $filter = GeoLocationFilter::make('location')
            ->coordinates('lat', 'lng')
            ->radius(25, 'mi')
            ->center(40.7128, -74.0060);

        expect($filter->getLatitudeColumn())->toBe('lat');
        expect($filter->getLongitudeColumn())->toBe('lng');
        expect($filter->getDefaultRadius())->toBe(25.0);
        expect($filter->getUnit())->toBe('mi');
    });
});

describe('Coordinate Validation', function () {
    it('accepts valid boundary coordinates (90, 180)', function () {
        $filter = GeoLocationFilter::make('location');

        $reflection = new ReflectionClass($filter);
        $indicateProp = $reflection->getProperty('indicateUsing');
        $indicateProp->setAccessible(true);
        $indicateFn = $indicateProp->getValue($filter);

        $bound = Closure::bind($indicateFn, $filter, get_class($filter));
        $result = $bound(['latitude' => '90', 'longitude' => '180', 'radius' => '10']);
        expect($result)->not->toBeEmpty();
    });

    it('accepts valid boundary coordinates (-90, -180)', function () {
        $filter = GeoLocationFilter::make('location');

        $reflection = new ReflectionClass($filter);
        $indicateProp = $reflection->getProperty('indicateUsing');
        $indicateProp->setAccessible(true);
        $indicateFn = $indicateProp->getValue($filter);

        $bound = Closure::bind($indicateFn, $filter, get_class($filter));
        $result = $bound(['latitude' => '-90', 'longitude' => '-180', 'radius' => '10']);
        expect($result)->not->toBeEmpty();
    });

    it('indicator returns empty for latitude > 90', function () {
        $filter = GeoLocationFilter::make('location');

        $reflection = new ReflectionClass($filter);
        $indicateProp = $reflection->getProperty('indicateUsing');
        $indicateProp->setAccessible(true);
        $indicateFn = $indicateProp->getValue($filter);

        $bound = Closure::bind($indicateFn, $filter, get_class($filter));
        $result = $bound(['latitude' => '91', 'longitude' => '0', 'radius' => '10']);
        expect($result)->toBeEmpty();
    });

    it('indicator returns empty for latitude < -90', function () {
        $filter = GeoLocationFilter::make('location');

        $reflection = new ReflectionClass($filter);
        $indicateProp = $reflection->getProperty('indicateUsing');
        $indicateProp->setAccessible(true);
        $indicateFn = $indicateProp->getValue($filter);

        $bound = Closure::bind($indicateFn, $filter, get_class($filter));
        $result = $bound(['latitude' => '-91', 'longitude' => '0', 'radius' => '10']);
        expect($result)->toBeEmpty();
    });

    it('indicator returns empty for longitude > 180', function () {
        $filter = GeoLocationFilter::make('location');

        $reflection = new ReflectionClass($filter);
        $indicateProp = $reflection->getProperty('indicateUsing');
        $indicateProp->setAccessible(true);
        $indicateFn = $indicateProp->getValue($filter);

        $bound = Closure::bind($indicateFn, $filter, get_class($filter));
        $result = $bound(['latitude' => '45', 'longitude' => '181', 'radius' => '10']);
        expect($result)->toBeEmpty();
    });

    it('indicator returns empty for longitude < -180', function () {
        $filter = GeoLocationFilter::make('location');

        $reflection = new ReflectionClass($filter);
        $indicateProp = $reflection->getProperty('indicateUsing');
        $indicateProp->setAccessible(true);
        $indicateFn = $indicateProp->getValue($filter);

        $bound = Closure::bind($indicateFn, $filter, get_class($filter));
        $result = $bound(['latitude' => '45', 'longitude' => '-181', 'radius' => '10']);
        expect($result)->toBeEmpty();
    });

    it('indicator returns empty for zero radius', function () {
        $filter = GeoLocationFilter::make('location');

        $reflection = new ReflectionClass($filter);
        $indicateProp = $reflection->getProperty('indicateUsing');
        $indicateProp->setAccessible(true);
        $indicateFn = $indicateProp->getValue($filter);

        $bound = Closure::bind($indicateFn, $filter, get_class($filter));
        $result = $bound(['latitude' => '45', 'longitude' => '90', 'radius' => '0']);
        expect($result)->toBeEmpty();
    });

    it('indicator returns empty for negative radius', function () {
        $filter = GeoLocationFilter::make('location');

        $reflection = new ReflectionClass($filter);
        $indicateProp = $reflection->getProperty('indicateUsing');
        $indicateProp->setAccessible(true);
        $indicateFn = $indicateProp->getValue($filter);

        $bound = Closure::bind($indicateFn, $filter, get_class($filter));
        $result = $bound(['latitude' => '45', 'longitude' => '90', 'radius' => '-5']);
        expect($result)->toBeEmpty();
    });
});
