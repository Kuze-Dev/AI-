<?php

return [
    'disks' => [
        'receipt-files' => [
            'driver' => env('FILESYSTEM_DISK', 's3'),
            'throw' => false,
        ],
    ],

    'days_before_due_date_notification' => 1,
];
