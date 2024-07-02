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
];