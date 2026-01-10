<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Range Filter Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for Range filters.
    |
    */
    'range' => [
        // Default date format
        'date_format' => 'Y-m-d',

        // Default datetime format
        'datetime_format' => 'Y-m-d H:i:s',

        // Default time format
        'time_format' => 'H:i:s',

        // Default placeholder for range inputs
        'placeholders' => [
            'from' => 'From',
            'to' => 'To',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Quick Filters Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for quick filters (Like, In, Comparison, etc).
    |
    */
    'quick_filters' => [
        // Default operator for LIKE queries
        'like_operator' => 'like', // 'like' or 'ilike'

        // Case sensitive for LIKE queries
        'case_sensitive' => false,

        // Wrap LIKE pattern with wildcards
        'like_wildcards' => 'both', // 'both', 'start', 'end', 'none'
    ],

    /*
    |--------------------------------------------------------------------------
    | Select Table Filter Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for SelectTableFilter.
    |
    */
    'select_table' => [
        // Maximum number of options to load
        'options_limit' => 100,
    ],

    /*
    |--------------------------------------------------------------------------
    | Modal Select Filter Security
    |--------------------------------------------------------------------------
    |
    | Configure security settings for ModalSelectFilter.
    |
    */
    'allowed_models' => [
        // Add model classes that are allowed to be queried via the modal select filter.
        // If empty, all models are allowed (not recommended for production).
        // Example:
        // App\Models\User::class,
        // App\Models\Category::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Filter Persistence Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for filter state persistence.
    |
    */
    'persistence' => [
        // Enable session persistence by default
        'session_enabled' => true,

        // Session key prefix
        'session_prefix' => 'filament-dcat-filters',

        // Enable LocalStorage persistence by default
        'local_storage_enabled' => false,

        // LocalStorage key prefix
        'local_storage_prefix' => 'filament-dcat-filters',

        // Automatically clear persistence on filter reset
        'clear_on_reset' => true,
    ],
];
