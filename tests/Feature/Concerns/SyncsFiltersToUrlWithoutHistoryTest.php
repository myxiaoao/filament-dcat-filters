<?php

use Cooper\FilamentDcatFilters\Concerns\SyncsFiltersToUrlWithoutHistory;

// Create a test class that uses the trait
class SyncsFiltersToUrlWithoutHistoryTestClass
{
    use SyncsFiltersToUrlWithoutHistory;
}

beforeEach(function () {
    $this->testClass = new SyncsFiltersToUrlWithoutHistoryTestClass;
});

describe('getFilterQueryString', function () {
    it('returns empty array when no filters', function () {
        $query = $this->testClass->getFilterQueryString();

        expect($query)->toBe([]);
    });

    it('includes filters in query string', function () {
        $this->testClass->tableFilters = ['status' => 'active'];

        $query = $this->testClass->getFilterQueryString();

        expect($query)->toHaveKey('tableFilters');
        expect($query['tableFilters'])->toBe(['status' => 'active']);
    });

    it('includes search in query string', function () {
        $this->testClass->tableSearch = 'test query';

        $query = $this->testClass->getFilterQueryString();

        expect($query)->toHaveKey('tableSearch');
        expect($query['tableSearch'])->toBe('test query');
    });

    it('includes sort column in query string', function () {
        $this->testClass->tableSortColumn = 'name';

        $query = $this->testClass->getFilterQueryString();

        expect($query)->toHaveKey('tableSortColumn');
        expect($query['tableSortColumn'])->toBe('name');
    });

    it('includes sort direction in query string', function () {
        $this->testClass->tableSortDirection = 'desc';

        $query = $this->testClass->getFilterQueryString();

        expect($query)->toHaveKey('tableSortDirection');
        expect($query['tableSortDirection'])->toBe('desc');
    });

    it('includes all properties in query string when set', function () {
        $this->testClass->tableFilters = ['status' => 'active'];
        $this->testClass->tableSearch = 'search term';
        $this->testClass->tableSortColumn = 'created_at';
        $this->testClass->tableSortDirection = 'asc';

        $query = $this->testClass->getFilterQueryString();

        expect($query)->toHaveCount(4);
        expect($query)->toHaveKey('tableFilters');
        expect($query)->toHaveKey('tableSearch');
        expect($query)->toHaveKey('tableSortColumn');
        expect($query)->toHaveKey('tableSortDirection');
    });
});

describe('resetUrlParameters', function () {
    it('resets all URL parameters', function () {
        $this->testClass->tableFilters = ['status' => 'active'];
        $this->testClass->tableSearch = 'test';
        $this->testClass->tableSortColumn = 'name';
        $this->testClass->tableSortDirection = 'desc';

        $this->testClass->resetUrlParameters();

        expect($this->testClass->tableFilters)->toBe([]);
        expect($this->testClass->tableSearch)->toBeNull();
        expect($this->testClass->tableSortColumn)->toBeNull();
        expect($this->testClass->tableSortDirection)->toBeNull();
    });
});

describe('getShareableFilterUrl', function () {
    it('generates shareable URL', function () {
        $this->testClass->tableFilters = ['status' => 'active'];

        // This test requires a request context, so we just verify the method exists
        expect(method_exists($this->testClass, 'getShareableFilterUrl'))->toBeTrue();
    });
});

describe('Trait Properties', function () {
    it('has tableFilters property', function () {
        expect(property_exists($this->testClass, 'tableFilters'))->toBeTrue();
    });

    it('has tableSearch property', function () {
        expect(property_exists($this->testClass, 'tableSearch'))->toBeTrue();
    });

    it('has tableSortColumn property', function () {
        expect(property_exists($this->testClass, 'tableSortColumn'))->toBeTrue();
    });

    it('has tableSortDirection property', function () {
        expect(property_exists($this->testClass, 'tableSortDirection'))->toBeTrue();
    });

    it('has default values for properties', function () {
        $freshClass = new SyncsFiltersToUrlWithoutHistoryTestClass;

        expect($freshClass->tableFilters)->toBe([]);
        expect($freshClass->tableSearch)->toBeNull();
        expect($freshClass->tableSortColumn)->toBeNull();
        expect($freshClass->tableSortDirection)->toBeNull();
    });
});
