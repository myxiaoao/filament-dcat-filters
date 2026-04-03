<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Illuminate\Support\Facades\Crypt;

trait HasFilterExportImport
{
    protected bool $encryptFilters = false;

    public function exportFilters(bool $formatted = false): string
    {
        $exportData = $this->getFilterExportData();
        $json = json_encode($exportData, $formatted ? JSON_PRETTY_PRINT : 0);

        return $this->encryptFilters ? Crypt::encryptString($json) : $json;
    }

    public function exportFiltersAsBase64(): string
    {
        return base64_encode($this->exportFilters(false));
    }

    public function importFilters(string $jsonString): bool
    {
        if ($this->encryptFilters) {
            try {
                $jsonString = Crypt::decryptString($jsonString);
            } catch (\Exception) {
                return false;
            }
        }

        $data = json_decode($jsonString, true);

        if (! is_array($data) || ! isset($data['filters'])) {
            return false;
        }

        if (! $this->isVersionCompatible($data['version'] ?? '1.0')) {
            return false;
        }

        $importFilters = $data['filters'];

        // If table filters are available, validate imported keys against descriptors
        if (method_exists($this, 'getTable')) {
            try {
                $tableFilters = $this->getTable()->getFilters();

                foreach ($importFilters as $name => $filterData) {
                    if (! isset($tableFilters[$name]) || ! is_array($filterData)) {
                        continue;
                    }

                    $filter = $tableFilters[$name];

                    if (method_exists($filter, 'getStateDescriptor')) {
                        $validFields = $filter->getStateDescriptor()->getFields();
                        $importFilters[$name] = array_intersect_key(
                            $filterData,
                            array_flip($validFields)
                        );
                    }
                }
            } catch (\Throwable) {
                // Fallback: use raw imported data if table is not available
            }
        }

        $this->tableFilters = $importFilters;
        $this->resetPage();

        return true;
    }

    public function importFiltersFromBase64(string $base64String): bool
    {
        $json = base64_decode($base64String, true);

        if ($json === false) {
            return false;
        }

        return $this->importFilters($json);
    }

    public function encryptFilters(bool $encrypt = true): static
    {
        $this->encryptFilters = $encrypt;

        return $this;
    }

    protected function isVersionCompatible(string $version): bool
    {
        return str_starts_with($version, '1.');
    }

    public function getFilterShareUrl(): string
    {
        return request()->url().'?filters='.urlencode($this->exportFiltersAsBase64());
    }

    public function loadFiltersFromUrl(): bool
    {
        $filters = request()->query('filters');

        if (empty($filters)) {
            return false;
        }

        return $this->importFiltersFromBase64($filters);
    }

    /** @return array{version: string, timestamp: string, filters: array} */
    public function getFilterExportData(): array
    {
        $filters = $this->tableFilters ?? [];

        // If table filters are available, use descriptors to skip empty filters
        if (method_exists($this, 'getTable')) {
            try {
                $tableFilters = $this->getTable()->getFilters();
                $cleanedFilters = [];

                foreach ($filters as $name => $data) {
                    if (! isset($tableFilters[$name])) {
                        continue;
                    }

                    $filter = $tableFilters[$name];

                    if (method_exists($filter, 'getStateDescriptor')) {
                        $descriptor = $filter->getStateDescriptor();

                        if (! $descriptor->isEmpty(is_array($data) ? $data : [])) {
                            $cleanedFilters[$name] = $data;
                        }
                    } else {
                        $cleanedFilters[$name] = $data;
                    }
                }

                $filters = $cleanedFilters;
            } catch (\Throwable) {
                // Fallback: use raw filters if table is not available
            }
        }

        return [
            'version' => '1.0',
            'timestamp' => now()->toIso8601String(),
            'filters' => $filters,
        ];
    }

    public function clearImportedFilters(): void
    {
        $this->tableFilters = [];
        $this->resetPage();
    }

    public function mergeFilters(string $jsonString, bool $overwrite = true): bool
    {
        // Handle decryption if enabled
        if ($this->encryptFilters) {
            try {
                $jsonString = Crypt::decryptString($jsonString);
            } catch (\Exception) {
                return false;
            }
        }

        $data = json_decode($jsonString, true);

        if (! is_array($data) || ! isset($data['filters'])) {
            return false;
        }

        $existingFilters = $this->tableFilters ?? [];
        $newFilters = $data['filters'];

        $this->tableFilters = $overwrite
            ? array_merge($existingFilters, $newFilters)
            : array_merge($newFilters, $existingFilters);

        $this->resetPage();

        return true;
    }
}
