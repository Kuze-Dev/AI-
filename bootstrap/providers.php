<?php

declare(strict_types=1);

return [

    /*
     * Package Service Providers...
     */

    /*
     * Application Service Providers...
     */
    App\Providers\SettingServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\AuthServiceProvider::class,
    // App\Providers\BroadcastServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    App\Providers\RouteServiceProvider::class,
    App\Providers\HealthCheckServiceProvider::class,
    App\Providers\TenancyServiceProvider::class,
    App\Providers\AboutServiceProvider::class,

    /*
     * filament
     */
    App\Providers\Filament\AdminPanelProvider::class,
    App\Providers\Filament\TenantPanelProvider::class,
    App\Providers\Filament\CommonServiceProvider::class,

    /*
     * Domain Service Providers...
     */
    Domain\Admin\AdminServiceProvider::class,
    Domain\Auth\AuthServiceProvider::class,
    Domain\Role\RoleServiceProvider::class,
    Domain\Blueprint\BlueprintServiceProvider::class,
    Domain\Shipment\ShippingMethodServiceProvider::class,
    Domain\Tier\TierServiceProvider::class,
    Domain\Tier\TierServiceProvider::class,
    Domain\Taxation\TaxationServiceProvider::class,
    Domain\ServiceOrder\ServiceOrderServiceProvider::class,

    /*
    * Support Service Providers...
    */
    Support\Captcha\CaptchaServiceProvider::class,
    Domain\Payments\PaymentServiceProvider::class,

    /**
     * Self hosts packages
     */

    HalcyonAgile\FilamentImport\ImportEventServiceProvider::class,
    HalcyonAgile\FilamentImport\ImportServiceProvider::class,

    HalcyonAgile\FilamentExport\FilamentExportServiceProvider::class,
    HalcyonAgile\FilamentExport\ExportEventServiceProvider::class,
];
