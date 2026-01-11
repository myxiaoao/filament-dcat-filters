<?php

namespace Cooper\FilamentDcatFilters\Concerns;

use Illuminate\Support\Facades\Crypt;

trait HasFilterExportImport
{
    /**
     * Whether to encrypt exported filter configurations.
     */
    protected bool $encryptFilters = false;

    /**
     * Export the current filter configuration as JSON.
     *
     * @param  bool  $formatted  Whether to format the JSON output
     */
    public function exportFilters(bool $formatted = false): string
    {
        $filters = $this->tableFilters ?? [];

        $exportData = [
            'version' => '1.0',
            'timestamp' => now()->toIso8601String(),
            'filters' => $filters,
        ];

        $json = json_encode($exportData, $formatted ? JSON_PRETTY_PRINT : 0);

        if ($this->encryptFilters) {
            return Crypt::encryptString($json);
        }

        return $json;
    }

    /**
     * Export the current filter configuration as a base64-encoded string.
     * Useful for URL sharing.
     */
    public function exportFiltersAsBase64(): string
    {
        $json = $this->exportFilters(false);

        return base64_encode($json);
    }

    /**
     * Import filter configuration from JSON.
     *
     * @param  string  $jsonString  The JSON string to import
     * @return bool Whether the import was successful
     */
    public function importFilters(string $jsonString): bool
    {
        try {
            // Try to decrypt if encryption is enabled
            if ($this->encryptFilters) {
                try {
                    $jsonString = Crypt::decryptString($jsonString);
                } catch (\Exception $e) {
                    // If decryption fails, try without decryption
                }
            }

            $data = json_decode($jsonString, true);

            if (! is_array($data) || ! isset($data['filters'])) {
                return false;
            }

            // Validate version compatibility
            $version = $data['version'] ?? '1.0';
            if (! $this->isVersionCompatible($version)) {
                return false;
            }

            $this->tableFilters = $data['filters'];
            $this->resetPage();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Import filter configuration from a base64-encoded string.
     *
     * @param  string  $base64String  The base64-encoded string to import
     * @return bool Whether the import was successful
     */
    public function importFiltersFromBase64(string $base64String): bool
    {
        $json = base64_decode($base64String, true);

        if ($json === false) {
            return false;
        }

        return $this->importFilters($json);
    }

    /**
     * Enable or disable filter encryption.
     */
    public function encryptFilters(bool $encrypt = true): static
    {
        $this->encryptFilters = $encrypt;

        return $this;
    }

    /**
     * Check if the exported version is compatible.
     */
    protected function isVersionCompatible(string $version): bool
    {
        // Currently support version 1.x
        return str_starts_with($version, '1.');
    }

    /**
     * Generate a shareable URL with the current filter configuration.
     * Override this method to customize the URL generation.
     */
    public function getFilterShareUrl(): string
    {
        $base64Filters = $this->exportFiltersAsBase64();

        // Get the current URL and append the filters parameter
        $currentUrl = request()->url();

        return $currentUrl . '?filters=' . urlencode($base64Filters);
    }

    /**
     * Load filters from URL parameters if present.
     */
    public function loadFiltersFromUrl(): bool
    {
        $filters = request()->query('filters');

        if (empty($filters)) {
            return false;
        }

        return $this->importFiltersFromBase64(urldecode($filters));
    }

    /**
     * Get the filter configuration as an array (for debugging or display).
     *
     * @return array{version: string, timestamp: string, filters: array}
     */
    public function getFilterExportData(): array
    {
        return [
            'version' => '1.0',
            'timestamp' => now()->toIso8601String(),
            'filters' => $this->tableFilters ?? [],
        ];
    }

    /**
     * Clear all imported filters.
     */
    public function clearImportedFilters(): void
    {
        $this->tableFilters = [];
        $this->resetPage();
    }

    /**
     * Merge imported filters with existing filters.
     *
     * @param  string  $jsonString  The JSON string to merge
     * @param  bool  $overwrite  Whether to overwrite existing filter values
     * @return bool Whether the merge was successful
     */
    public function mergeFilters(string $jsonString, bool $overwrite = true): bool
    {
        try {
            $data = json_decode($jsonString, true);

            if (! is_array($data) || ! isset($data['filters'])) {
                return false;
            }

            $existingFilters = $this->tableFilters ?? [];
            $newFilters = $data['filters'];

            if ($overwrite) {
                $this->tableFilters = array_merge($existingFilters, $newFilters);
            } else {
                $this->tableFilters = array_merge($newFilters, $existingFilters);
            }

            $this->resetPage();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
