<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Table Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default table behavior when the package is installed.
    | These settings are applied globally via Table::configureUsing().
    |
    */
    'table' => [
        // Display filters above table content (FiltersLayout::AboveContent)
        'filters_above_content' => true,

        // Display reset action in the footer (FiltersResetActionPosition::Footer)
        'reset_action_in_footer' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Configuration
    |--------------------------------------------------------------------------
    |
    | Configure database driver detection and behavior for filters that
    | generate driver-specific SQL (LIKE/ILIKE, REGEXP, FIND_IN_SET, etc).
    |
    */
    'database' => [
        // Database driver: 'auto' (detect from connection), 'mysql', 'pgsql', 'sqlite'
        'driver' => 'auto',

        // Global default for case-insensitive search
        'case_insensitive' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Range Filter Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for Range filters.
    |
    */
    'range' => [
        // Default date format (storage format)
        'date_format' => 'Y-m-d',

        // Default date display format (UI display)
        'date_display_format' => 'Y-m-d',

        // Default datetime format (storage format)
        'datetime_format' => 'Y-m-d H:i:s',

        // Default datetime display format (UI display)
        'datetime_display_format' => 'Y-m-d H:i:s',

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
    | Boolean Filter Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for Boolean filters.
    |
    */
    'boolean' => [
        // Default display style: 'select', 'radio', or 'toggle'
        'default_display_style' => 'select',

        // Default labels
        'default_true_label' => 'Yes',
        'default_false_label' => 'No',
        'default_all_label' => 'All',
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
