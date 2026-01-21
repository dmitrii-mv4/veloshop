<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Force HTTPS
    |--------------------------------------------------------------------------
    |
    | This determines whether HTTPS should be forced. You can set:
    | - true/false: Always on/off
    | - 'auto': Detect automatically
    | - array: List of environments where HTTPS is forced
    |
    */
    'force_https' => env('FORCE_HTTPS', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | HTTPS Environments
    |--------------------------------------------------------------------------
    |
    | List of environments where HTTPS should be enforced.
    |
    */
    'https_environments' => ['production', 'staging'],

    /*
    |--------------------------------------------------------------------------
    | Trusted Proxies
    |--------------------------------------------------------------------------
    |
    | List of trusted proxy IP addresses.
    |
    */
    'trusted_proxies' => env('TRUSTED_PROXIES', ''),
];
