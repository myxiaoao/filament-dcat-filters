<?php

use Cooper\FilamentDcatFilters\Filters\GeoLocationFilter;

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
