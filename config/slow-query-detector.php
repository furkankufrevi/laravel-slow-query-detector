<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Slow Query Detector
    |--------------------------------------------------------------------------
    |
    | Toggle the detector on or off. You'll typically want this enabled
    | only in local/staging environments.
    |
    */
    'enabled' => env('SLOW_QUERY_DETECTOR_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Slow Query Threshold (ms)
    |--------------------------------------------------------------------------
    |
    | Queries that take longer than this value (in milliseconds) will be
    | logged as slow queries.
    |
    */
    'threshold' => env('SLOW_QUERY_THRESHOLD', 100),

    /*
    |--------------------------------------------------------------------------
    | N+1 Detection
    |--------------------------------------------------------------------------
    |
    | When enabled, the detector will identify duplicate queries that
    | indicate an N+1 problem. 'duplicate_threshold' is the number of
    | times a query pattern must repeat to trigger a warning.
    |
    */
    'detect_n_plus_one' => true,
    'duplicate_threshold' => 3,

    /*
    |--------------------------------------------------------------------------
    | Log Channel
    |--------------------------------------------------------------------------
    |
    | The log channel to use for writing warnings. Set to null to use
    | Laravel's default channel.
    |
    */
    'log_channel' => env('SLOW_QUERY_LOG_CHANNEL', null),

    /*
    |--------------------------------------------------------------------------
    | Include Stack Trace
    |--------------------------------------------------------------------------
    |
    | When enabled, log entries will include a short stack trace showing
    | where the query originated in your application code.
    |
    */
    'include_trace' => true,
    'trace_depth' => 5,

];
