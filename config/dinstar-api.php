<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Gateway Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of the Dinstar GSM gateway. The package appends API paths
    | (for example, "/api/send_sms") to this value, so it should NOT include
    | a trailing slash.
    |
    */

    'url' => env('DINSTAR_API_URL'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Basic Auth Credentials
    |--------------------------------------------------------------------------
    |
    | The Dinstar gateway uses HTTP Basic auth for every API call. The default
    | credentials shipped by the manufacturer are admin / admin — change them
    | on the gateway and reflect them here.
    |
    */

    'username' => env('DINSTAR_API_USERNAME', 'admin'),
    'password' => env('DINSTAR_API_PASSWORD', 'admin'),

    /*
    |--------------------------------------------------------------------------
    | HTTP Client Options
    |--------------------------------------------------------------------------
    |
    | timeout      — request timeout in seconds.
    | retry_times  — number of times to retry a failed request.
    | retry_sleep  — milliseconds between retries.
    | verify       — whether to verify SSL certificates. Most gateways ship
    |                with self-signed certificates, so this defaults to false.
    |
    */

    'timeout' => (int) env('DINSTAR_API_TIMEOUT', 10),
    'retry_times' => (int) env('DINSTAR_API_RETRY_TIMES', 3),
    'retry_sleep' => (int) env('DINSTAR_API_RETRY_SLEEP', 100),
    'verify' => (bool) env('DINSTAR_API_VERIFY_SSL', false),

];
