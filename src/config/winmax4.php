<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Licenses Configuration
    |--------------------------------------------------------------------------
    |
    | This value determines whether the package should use licenses or not.
    | If set to true, the package will expect a license to be provided
    | when creating a new Winmax4 setting. If set to false, the package
    | will not require a license to be provided when creating a new
    | Winmax4 setting.
    |
    */
    'use_license' => env('WINMAX4_USE_LICENSE', false),
    'license_is_uuid' => env('WINMAX4_LICENSE_IS_UUID', false),
    'license_column' => env('WINMAX4_LICENSE_COLUMN', 'license_id'),
    'licenses_table' => env('WINMAX4_LICENSES_TABLE', 'licenses'),


    /*
    |--------------------------------------------------------------------------
    | Guzzle Configuration
    |--------------------------------------------------------------------------
    |
    | This value determines whether Guzzle should verify the SSL certificate
    | of the Winmax4 API. If set to true, Guzzle will verify the SSL
    | certificate of the Winmax4 API. If set to false, Guzzle will
    | not verify the SSL certificate of the Winmax4 API.
    |
    */
    'verify_ssl_guzzle' => env('WINMAX4_VERIFY_SSL_GUZZLE', true),
];