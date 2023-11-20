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

    'import_expires_in_minute' => 1_440, // 1 day, in-case of large import filze via queue

    'path' => 'admin/download-export',

    'middleware' => [
        'universal',
        InitializeTenancyByDomain::class,
    ]

];
