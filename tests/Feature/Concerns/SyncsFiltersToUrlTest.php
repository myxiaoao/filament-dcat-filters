<?php

use Cooper\FilamentDcatFilters\Concerns\SyncsFiltersToUrl;
use Illuminate\Http\Request;

// Create a mock class that uses the trait
class MockUrlSyncComponent
{
    use SyncsFiltersToUrl;

    public function __construct()
    {
        // Initialize properties
        $this->tableFilters = [];
        $this->tableSearch = null;
        $this->tableSortColumn = null;
        $this->tableSortDirection = null;
    }
}

it('returns empty query string when no filters', function () {
    $component = new MockUrlSyncComponent;

    $query = $component->getFilterQueryString();

    expect($query)->toBe([]);
});

it('includes filters in query string', function () {
    $component = new MockUrlSyncComponent;
    $component->tableFilters = ['status' => ['value' => 'active']];

    $query = $component->getFilterQueryString();

    expect($query)->toHaveKey('tableFilters');
    expect($query['tableFilters'])->toBe(['status' => ['value' => 'active']]);
});

it('includes search in query string', function () {
    $component = new MockUrlSyncComponent;
    $component->tableSearch = 'hello world';

    $query = $component->getFilterQueryString();

    expect($query)->toHaveKey('tableSearch');
    expect($query['tableSearch'])->toBe('hello world');
});

it('includes sort in query string', function () {
    $component = new MockUrlSyncComponent;
    $component->tableSortColumn = 'created_at';
    $component->tableSortDirection = 'desc';

    $query = $component->getFilterQueryString();

    expect($query)->toHaveKey('tableSortColumn');
    expect($query)->toHaveKey('tableSortDirection');
    expect($query['tableSortColumn'])->toBe('created_at');
    expect($query['tableSortDirection'])->toBe('desc');
});

it('includes all properties in query string when set', function () {
    $component = new MockUrlSyncComponent;
    $component->tableFilters = ['status' => ['value' => 'active']];
    $component->tableSearch = 'test';
    $component->tableSortColumn = 'title';
    $component->tableSortDirection = 'asc';

    $query = $component->getFilterQueryString();

    expect($query)->toHaveCount(4);
    expect($query)->toHaveKey('tableFilters');
    expect($query)->toHaveKey('tableSearch');
    expect($query)->toHaveKey('tableSortColumn');
    expect($query)->toHaveKey('tableSortDirection');
});

it('resets all URL parameters', function () {
    $component = new MockUrlSyncComponent;
    $component->tableFilters = ['status' => ['value' => 'active']];
    $component->tableSearch = 'test';
    $component->tableSortColumn = 'title';
    $component->tableSortDirection = 'asc';

    $component->resetUrlParameters();

    expect($component->tableFilters)->toBe([]);
    expect($component->tableSearch)->toBeNull();
    expect($component->tableSortColumn)->toBeNull();
    expect($component->tableSortDirection)->toBeNull();
});

it('generates shareable URL', function () {
    // Mock the request
    $request = Request::create('https://example.com/posts', 'GET');
    app()->instance('request', $request);

    $component = new MockUrlSyncComponent;
    $component->tableFilters = ['status' => ['value' => 'active']];

    $url = $component->getShareableFilterUrl();

    expect($url)->toContain('https://example.com/posts');
    expect($url)->toContain('tableFilters');
});

it('returns base URL when no filters for shareable URL', function () {
    // Mock the request
    $request = Request::create('https://example.com/posts', 'GET');
    app()->instance('request', $request);

    $component = new MockUrlSyncComponent;

    $url = $component->getShareableFilterUrl();

    expect($url)->toBe('https://example.com/posts');
});
