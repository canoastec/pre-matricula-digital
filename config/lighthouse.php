<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Security Settings
    |--------------------------------------------------------------------------
    |
    | Control how the GraphQL API behaves regarding security.
    |
    */

    'security' => [
        'max_query_complexity' => env('LIGHTHOUSE_MAX_QUERY_COMPLEXITY', 100),
        'max_query_depth' => env('LIGHTHOUSE_MAX_QUERY_DEPTH', 4),
        'disable_introspection' => env('LIGHTHOUSE_DISABLE_INTROSPECTION', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Limits the maximum amount of items that can be requested in a single
    | query to prevent fetching too many records at once.
    |
    */

    'pagination' => [
        'default_count' => null,
        'max_count' => 50,
    ],
];
