<?php

use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

return [

    'temporary_files' => [

        'disk' => null,

        'base_directory' => 'filament-excel',

        'local_permissions' => [
            'dir'  => 0755,
            'file' => 0644,
        ],

    ],

    'export_expires_in_minute' => 30,

    'path' => 'admin/download-export',

    'middleware' => [
        'universal',
        InitializeTenancyByDomain::class,
    ]

];
