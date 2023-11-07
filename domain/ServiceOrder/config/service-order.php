<?php

return [
    'disks' => [
        'receipt-files' => [
            'driver' => env('FILESYSTEM_DISK', 's3'),
            'throw' => false,
        ],
    ],
];
