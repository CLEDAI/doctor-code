<?php

return [
    /*
    |--------------------------------------------------------------------------
    | DoctorCode API Key
    |--------------------------------------------------------------------------
    |
    | Your DoctorCode API key. Get one at https://doctorcode.dev
    |
    */
    'api_key' => env('DOCTORCODE_API_KEY', ''),

    /*
    |--------------------------------------------------------------------------
    | DoctorCode API URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the DoctorCode API. You typically don't need to change this.
    |
    */
    'api_url' => env('DOCTORCODE_API_URL', 'https://doctorcode.dev/api'),

    /*
    |--------------------------------------------------------------------------
    | Enabled
    |--------------------------------------------------------------------------
    |
    | Enable or disable DoctorCode error reporting globally.
    |
    */
    'enabled' => env('DOCTORCODE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Report in Local Environment
    |--------------------------------------------------------------------------
    |
    | Whether to report errors to DoctorCode when running in local environment.
    | Set to false to only report errors in production/staging.
    |
    */
    'report_local' => env('DOCTORCODE_REPORT_LOCAL', false),

    /*
    |--------------------------------------------------------------------------
    | Don't Report
    |--------------------------------------------------------------------------
    |
    | Exception types that should not be reported to DoctorCode.
    |
    */
    'dont_report' => [
        \Illuminate\Auth\AuthenticationException::class,
        \Illuminate\Validation\ValidationException::class,
        \Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class,
        \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException::class,
    ],
];
