<?php
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Http\Middleware\MirrorConfigToSubpackages;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;

return [
    'temporary_files' => [

        'disk' => env('FILESYSTEM_DISK', 's3'),

        'base_directory' => 'filament-export',
    ],

    'user_timezone_field' => 'timezone',

    'expires_in_minute' => 60,

//    'http' => [
//        'route' => [
//            'name' => 'filament-export.download',
//            'path' => 'admin/export/download',
//            'middleware' => [
//
//                Authenticate::class,
//                'verified:filament.auth.verification.notice',
//                'active:filament.auth.account-deactivated.notice',
//
//                EncryptCookies::class,
//                AddQueuedCookiesToResponse::class,
//                StartSession::class,
//                // AuthenticateSession::class,
//                ShareErrorsFromSession::class,
//                VerifyCsrfToken::class,
//                SubstituteBindings::class,
//                DispatchServingFilamentEvent::class,
//                MirrorConfigToSubpackages::class,
//
//
//                'universal',
//                InitializeTenancyByDomain::class,
//
//                'tenant',
//            ]
//        ]
//    ]

];
