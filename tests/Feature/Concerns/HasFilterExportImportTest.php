<?php

use Cooper\FilamentDcatFilters\Concerns\HasFilterExportImport;

// Create a test class that uses the trait
class TestListRecordsWithExportImport
{
    use HasFilterExportImport {
        exportFilters as public;
        exportFiltersAsBase64 as public;
        importFilters as public;
        importFiltersFromBase64 as public;
        encryptFilters as public;
        getFilterShareUrl as public;
        getFilterExportData as public;
        clearImportedFilters as public;
        mergeFilters as public;
    }

    public array $tableFilters = [];

    protected bool $pageReset = false;

    public function resetPage(): void
    {
        $this->pageReset = true;
    }

    public function wasPageReset(): bool
    {
        return $this->pageReset;
    }
}

beforeEach(function () {
    $this->listRecords = new TestListRecordsWithExportImport;
});

describe('Export', function () {
    it('can export empty filters', function () {
        $json = $this->listRecords->exportFilters();
        $data = json_decode($json, true);

        expect($data)->toHaveKey('version');
        expect($data)->toHaveKey('timestamp');
        expect($data)->toHaveKey('filters');
        expect($data['filters'])->toBe([]);
    });

    it('can export filters with data', function () {
        $this->listRecords->tableFilters = [
            'status' => ['value' => 'active'],
            'category' => ['value' => 'tech'],
        ];

        $json = $this->listRecords->exportFilters();
        $data = json_decode($json, true);

        expect($data['filters'])->toBe([
            'status' => ['value' => 'active'],
            'category' => ['value' => 'tech'],
        ]);
    });

    it('can export formatted JSON', function () {
        $this->listRecords->tableFilters = ['status' => ['value' => 'active']];

        $json = $this->listRecords->exportFilters(formatted: true);

        expect($json)->toContain("\n");
    });

    it('can export as base64', function () {
        $this->listRecords->tableFilters = ['status' => ['value' => 'active']];

        $base64 = $this->listRecords->exportFiltersAsBase64();

        expect(base64_decode($base64, true))->not->toBeFalse();
    });
});

describe('Import', function () {
    it('can import valid JSON', function () {
        $json = json_encode([
            'version' => '1.0',
            'timestamp' => now()->toIso8601String(),
            'filters' => ['status' => ['value' => 'active']],
        ]);

        $result = $this->listRecords->importFilters($json);

        expect($result)->toBeTrue();
        expect($this->listRecords->tableFilters)->toBe(['status' => ['value' => 'active']]);
        expect($this->listRecords->wasPageReset())->toBeTrue();
    });

    it('returns false for invalid JSON', function () {
        $result = $this->listRecords->importFilters('not valid json');

        expect($result)->toBeFalse();
    });

    it('returns false for JSON without filters key', function () {
        $result = $this->listRecords->importFilters('{"version": "1.0"}');

        expect($result)->toBeFalse();
    });

    it('can import from base64', function () {
        $json = json_encode([
            'version' => '1.0',
            'timestamp' => now()->toIso8601String(),
            'filters' => ['category' => ['value' => 'news']],
        ]);
        $base64 = base64_encode($json);

        $result = $this->listRecords->importFiltersFromBase64($base64);

        expect($result)->toBeTrue();
        expect($this->listRecords->tableFilters)->toBe(['category' => ['value' => 'news']]);
    });

    it('returns false for invalid base64', function () {
        $result = $this->listRecords->importFiltersFromBase64('!!!not-valid-base64!!!');

        expect($result)->toBeFalse();
    });
});

describe('Export Data', function () {
    it('can get filter export data as array', function () {
        $this->listRecords->tableFilters = ['status' => ['value' => 'active']];

        $data = $this->listRecords->getFilterExportData();

        expect($data)->toHaveKey('version');
        expect($data)->toHaveKey('timestamp');
        expect($data)->toHaveKey('filters');
        expect($data['version'])->toBe('1.0');
    });
});

describe('Clear', function () {
    it('can clear imported filters', function () {
        $this->listRecords->tableFilters = ['status' => ['value' => 'active']];

        $this->listRecords->clearImportedFilters();

        expect($this->listRecords->tableFilters)->toBe([]);
        expect($this->listRecords->wasPageReset())->toBeTrue();
    });
});

describe('Merge', function () {
    it('can merge filters with overwrite', function () {
        $this->listRecords->tableFilters = ['status' => ['value' => 'inactive']];

        $json = json_encode([
            'version' => '1.0',
            'filters' => ['status' => ['value' => 'active'], 'category' => ['value' => 'news']],
        ]);

        $result = $this->listRecords->mergeFilters($json, overwrite: true);

        expect($result)->toBeTrue();
        expect($this->listRecords->tableFilters['status'])->toBe(['value' => 'active']);
        expect($this->listRecords->tableFilters['category'])->toBe(['value' => 'news']);
    });

    it('can merge filters without overwrite', function () {
        $this->listRecords->tableFilters = ['status' => ['value' => 'inactive']];

        $json = json_encode([
            'version' => '1.0',
            'filters' => ['status' => ['value' => 'active'], 'category' => ['value' => 'news']],
        ]);

        $result = $this->listRecords->mergeFilters($json, overwrite: false);

        expect($result)->toBeTrue();
        expect($this->listRecords->tableFilters['status'])->toBe(['value' => 'inactive']);
        expect($this->listRecords->tableFilters['category'])->toBe(['value' => 'news']);
    });

    it('returns false for invalid JSON', function () {
        $result = $this->listRecords->mergeFilters('not valid');

        expect($result)->toBeFalse();
    });
});

describe('Encryption', function () {
    it('can enable encryption', function () {
        $this->listRecords->encryptFilters(true);

        // Just verify the method doesn't throw
        expect(true)->toBeTrue();
    });

    it('can disable encryption', function () {
        $this->listRecords->encryptFilters(false);

        expect(true)->toBeTrue();
    });

    it('encrypted export and import roundtrip works', function () {
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);

        $originalFilters = ['status' => ['value' => 'active']];

        $this->listRecords->encryptFilters(true);
        $this->listRecords->tableFilters = $originalFilters;
        $exported = $this->listRecords->exportFilters();

        $newInstance = new TestListRecordsWithExportImport;
        $newInstance->encryptFilters(true);
        $result = $newInstance->importFilters($exported);

        expect($result)->toBeTrue();
        expect($newInstance->tableFilters)->toBe($originalFilters);
    });

    it('importing encrypted data without encryption enabled returns false', function () {
        config(['app.key' => 'base64:'.base64_encode(random_bytes(32))]);

        $this->listRecords->encryptFilters(true);
        $this->listRecords->tableFilters = ['status' => ['value' => 'active']];
        $encrypted = $this->listRecords->exportFilters();

        // New instance with encryption disabled tries to import encrypted payload
        $newInstance = new TestListRecordsWithExportImport;
        $result = $newInstance->importFilters($encrypted);

        expect($result)->toBeFalse();
    });
});

describe('URL Loading', function () {
    it('loadFiltersFromUrl returns false when query param is absent', function () {
        // No 'filters' query parameter in the request
        $result = $this->listRecords->loadFiltersFromUrl();

        expect($result)->toBeFalse();
    });
});

describe('Version Compatibility', function () {
    it('returns false when version is incompatible', function () {
        $json = json_encode([
            'version' => '2.0',
            'timestamp' => now()->toIso8601String(),
            'filters' => ['status' => ['value' => 'active']],
        ]);

        $result = $this->listRecords->importFilters($json);

        expect($result)->toBeFalse();
    });
});

describe('Roundtrip', function () {
    it('can export and import roundtrip', function () {
        $originalFilters = [
            'status' => ['value' => 'active'],
            'category' => ['value' => 'tech'],
            'date_range' => ['from' => '2024-01-01', 'to' => '2024-12-31'],
        ];

        $this->listRecords->tableFilters = $originalFilters;
        $exported = $this->listRecords->exportFilters();

        $newInstance = new TestListRecordsWithExportImport;
        $newInstance->importFilters($exported);

        expect($newInstance->tableFilters)->toBe($originalFilters);
    });

    it('can export and import base64 roundtrip', function () {
        $originalFilters = ['status' => ['value' => 'pending']];

        $this->listRecords->tableFilters = $originalFilters;
        $exported = $this->listRecords->exportFiltersAsBase64();

        $newInstance = new TestListRecordsWithExportImport;
        $newInstance->importFiltersFromBase64($exported);

        expect($newInstance->tableFilters)->toBe($originalFilters);
    });
});
