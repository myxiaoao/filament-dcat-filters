<?php

use Cooper\FilamentDcatFilters\Concerns\HasScopeBadgeCounts;

// Create a test class that uses the trait
class TestListRecordsWithBadgeCounts
{
    use HasScopeBadgeCounts {
        getScopeBadgeCount as public;
        getAllScopeBadgeCounts as public;
        clearScopeBadgeCountCache as public;
        refreshScopeBadgeCount as public;
        areScopeBadgeCountsEnabled as public;
        formatScopeBadgeCount as public;
        registerScopesForBadgeCounts as public;
        scopeBadgeCounts as public;
    }
}

beforeEach(function () {
    $this->listRecords = new TestListRecordsWithBadgeCounts;
});

it('is enabled by default', function () {
    expect($this->listRecords->areScopeBadgeCountsEnabled())->toBeTrue();
});

it('can be disabled', function () {
    $this->listRecords->scopeBadgeCounts(false);

    expect($this->listRecords->areScopeBadgeCountsEnabled())->toBeFalse();
});

it('can be enabled', function () {
    $this->listRecords->scopeBadgeCounts(false);
    $this->listRecords->scopeBadgeCounts(true);

    expect($this->listRecords->areScopeBadgeCountsEnabled())->toBeTrue();
});

it('returns null when badge counts are disabled', function () {
    $this->listRecords->registerScopesForBadgeCounts([
        'published' => ['query' => fn ($q) => $q],
    ]);
    $this->listRecords->scopeBadgeCounts(false);

    expect($this->listRecords->getScopeBadgeCount('published'))->toBeNull();
});

it('returns null for undefined scope', function () {
    expect($this->listRecords->getScopeBadgeCount('undefined'))->toBeNull();
});

it('can register scope definitions', function () {
    $this->listRecords->registerScopesForBadgeCounts([
        'published' => ['query' => fn ($q) => $q],
        'draft' => ['query' => fn ($q) => $q],
    ]);

    // Just verify it doesn't throw
    expect(true)->toBeTrue();
});

it('can clear cache', function () {
    $this->listRecords->clearScopeBadgeCountCache();

    // No exception means success
    expect(true)->toBeTrue();
});

it('returns empty array when disabled', function () {
    $this->listRecords->registerScopesForBadgeCounts([
        'published' => ['query' => fn ($q) => $q],
    ]);
    $this->listRecords->scopeBadgeCounts(false);

    $counts = $this->listRecords->getAllScopeBadgeCounts();

    expect($counts)->toBe([]);
});

describe('Format Badge Count', function () {
    it('formats numbers under 1000 as is', function () {
        expect($this->listRecords->formatScopeBadgeCount(0))->toBe('0');
        expect($this->listRecords->formatScopeBadgeCount(1))->toBe('1');
        expect($this->listRecords->formatScopeBadgeCount(999))->toBe('999');
    });

    it('formats thousands with K', function () {
        expect($this->listRecords->formatScopeBadgeCount(1000))->toBe('1K');
        expect($this->listRecords->formatScopeBadgeCount(1500))->toBe('1.5K');
        expect($this->listRecords->formatScopeBadgeCount(9999))->toBe('10K');
    });

    it('formats millions with M', function () {
        expect($this->listRecords->formatScopeBadgeCount(1000000))->toBe('1M');
        expect($this->listRecords->formatScopeBadgeCount(1500000))->toBe('1.5M');
        expect($this->listRecords->formatScopeBadgeCount(2500000))->toBe('2.5M');
    });
});
