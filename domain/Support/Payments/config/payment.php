<?php

return [
   
    'default' => 'cod',

        'manual' => [],

        'paypal' => [

            'http.ConnectionTimeOut' => 30,
            'log.LogEnabled' => true,
            'log.FileName' => storage_path() . '/logs/paypal.log',
            'log.LogLevel' => 'ERROR',
        ],
];
