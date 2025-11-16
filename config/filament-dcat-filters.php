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
];
