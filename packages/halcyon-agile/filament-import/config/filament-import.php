<?php

return [

    'temporary_files' => [

        'disk' => null,

        'base_directory' => 'filament-import',
    ],

    'expires_in_minute' => 1_440, // 1 day, in-case of large import filze via queue
];
