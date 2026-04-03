<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Inline Label (Dcat Admin Style)
    |--------------------------------------------------------------------------
    |
    | When enabled, filter labels are displayed inside the input as a prefix
    | (dcat-admin style) instead of above the input (Filament default).
    |
    */
    'inline_label' => true,

    /*
    |--------------------------------------------------------------------------
    | Placeholder From Label
    |--------------------------------------------------------------------------
    |
    | When enabled, the placeholder text defaults to the label text.
    | This provides a consistent dcat-admin experience where the label
    | serves as both prefix and placeholder hint.
    |
    */
    'placeholder_from_label' => true,

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

        // Number of columns in the filters form (null = Filament default)
        'filters_form_columns' => 4,
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
        // Note: Only MySQL, PostgreSQL, and SQLite are officially supported.
        // SQL Server and Oracle are not supported — driver-specific filters
        // (FullTextFilter, RegexFilter, FindInSetFilter) may not work correctly on those drivers.
        'driver' => 'auto',
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
    | Modal Select Table Configuration
    |--------------------------------------------------------------------------
    |
    | Configure pagination options for the modal select table component.
    |
    */
    'modal_select' => [
        'pagination_options' => [10, 25, 50],
        'default_pagination' => 10,
    ],

    /*
    |--------------------------------------------------------------------------
    | Regex Filter Configuration
    |--------------------------------------------------------------------------
    |
    | Configure default behavior for Regex filters.
    |
    */
    'regex' => [
        // Maximum allowed pattern length (ReDoS mitigation)
        'max_pattern_length' => 500,
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
        // IMPORTANT: When empty, ALL requests are denied for security.
        // You must explicitly list allowed models.
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
        // Session key prefix
        'session_prefix' => 'filament-dcat-filters',
    ],
];
