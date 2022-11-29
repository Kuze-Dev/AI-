<?php

return [
    /*
     *  Automatic registration of routes will only happen if this setting is `true`
     */
    'enabled' => true,

    /*
     * Controllers in these directories that have routing attributes
     * will automatically be registered.
     *
     * Optionally, you can specify group configuration by using key/values
     */
    'directories' => [
        app_path('Http/Controllers'),
        app_path('HttpApi/Controllers') => [
            'prefix' => 'api',
            'as' => 'api.',
            'middleware' => 'api',
        ],
        // app_path('HttpTenant/Controllers') => [
        //     'as' => 'tenant.',
        //     'middleware' => ['tenant', 'web'],
        // ],
        app_path('HttpTenantApi/Controllers') => [
            'prefix' => 'api',
            'as' => 'tenant.api.',
            'middleware' => ['tenant', 'api'],
        ],
    ],

    /**
     * This middleware will be applied to all routes.
     */
    'middleware' => [
        \Illuminate\Routing\Middleware\SubstituteBindings::class
    ]
];
