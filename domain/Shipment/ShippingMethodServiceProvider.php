<?php

declare(strict_types=1);

namespace Domain\Shipment;

use App\Settings\ShippingSettings;
use Domain\Shipment\API\USPS\Clients\AddressClient;
use Domain\Shipment\API\USPS\Clients\Client;
use Domain\Shipment\API\USPS\Clients\RateClient;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\Drivers\StorePickup;
use Domain\Shipment\Drivers\UspsDriver;
use Domain\ShippingMethod\Enums\Driver;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class ShippingMethodServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register(): void
    {
        $this->app->singleton(
            ShippingManagerInterface::class,
            fn ($app) => $app->make(ShippingManager::class)
        );

        $this->app->bind(
            Client::class,
            function () {
                $setting = app(ShippingSettings::class);

                return new Client(
                    username: $setting->getUsername(),
                    password: $setting->getPassword(),
                    isProduction: $setting->usps_mode,
                );
            }
        );

        $this->mergeConfigFrom(__DIR__ . '/config/shipment.php', 'domain.shipment');
    }

    public function boot(): void
    {
        if (tenancy()->initialized) {

            $shippingMethods = ShippingMethod::whereStatus(true)->get();

            if ($shippingMethods->isNotEmpty()) {
                foreach ($shippingMethods as $shippingMethod) {
                    app(ShippingManagerInterface::class)
                        ->extend(
                            $shippingMethod->slug,
                            fn () => match ($shippingMethod->driver) {
                                Driver::STORE_PICKUP => new StorePickup(),
                                Driver::USPS => new UspsDriver(app(RateClient::class), app(AddressClient::class)),
                                default => throw new InvalidArgumentException(),
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
            Client::class,
        ];
    }
}
