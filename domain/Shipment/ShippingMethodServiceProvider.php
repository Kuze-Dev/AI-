<?php

declare(strict_types=1);

namespace Domain\Shipment;

use App\Settings\ShippingSettings;
use Domain\Shipment\API\USPS\Clients\Client as USPSClient;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\Drivers\StorePickupDriver;
use Domain\Shipment\Drivers\UspsDriver;
use Domain\ShippingMethod\Enums\Driver;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;

class ShippingMethodServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ShippingManagerInterface::class,
            fn ($app) => $app->make(ShippingManager::class)
        );

        $this->app->bind(
            USPSClient::class,
            function () {
                $setting = app(ShippingSettings::class);

                if ($setting->usps_username === null || $setting->usps_password === null) {
                    abort(500, 'Setting USPS credential not setup yet.');
                }

                return new USPSClient(
                    username: $setting->usps_username,
                    password: $setting->usps_password,
                    isProduction: $setting->usps_production_mode,
                );
            }
        );

        $this->mergeConfigFrom(__DIR__ . '/config/shipment.php', 'domain.shipment');
    }

    public function boot(): void
    {
        if (tenancy()->initialized) {

            $shippingMethods = ShippingMethod::whereActive(true);

            if ($shippingMethods->count() > 0) {
                foreach ($shippingMethods->get() as $shippingMethod) {
                    app(ShippingManagerInterface::class)
                        ->extend(
                            $shippingMethod->driver->value,
                            fn () => match ($shippingMethod->driver) {
                                Driver::STORE_PICKUP => new StorePickupDriver(),
                                Driver::USPS => new UspsDriver(),
                            }
                        );
                }
            }
        }
    }

    public function provides(): array
    {
        return [
            ShippingManagerInterface::class,
            USPSClient::class,
        ];
    }
}
