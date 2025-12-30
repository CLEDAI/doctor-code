<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DoctorCode API Key
    |--------------------------------------------------------------------------
    |
    | Your DoctorCode API key from https://doctorcode.dev/user/dashboard
    |
    */
    'api_key' => env('DOCTORCODE_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Enable Error Reporting
    |--------------------------------------------------------------------------
    |
    | Enable or disable error reporting to DoctorCode
    |
    */
    'enabled' => env('DOCTORCODE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | The environment name (production, staging, development)
    |
    */
    'environment' => env('DOCTORCODE_ENVIRONMENT', env('APP_ENV', 'production')),

    /*
    |--------------------------------------------------------------------------
    | Auto Capture Exceptions
    |--------------------------------------------------------------------------
    |
    | Automatically capture all exceptions in Laravel's exception handler
    |
    */
    'auto_capture' => env('DOCTORCODE_AUTO_CAPTURE', true),

    /*
    |--------------------------------------------------------------------------
    | API Timeout
    |--------------------------------------------------------------------------
    |
    | Timeout in seconds for API requests
    |
    */
    'timeout' => env('DOCTORCODE_TIMEOUT', 5),
];
