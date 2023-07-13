<?php

declare(strict_types=1);

namespace Domain\Shipment;

use App\Settings\ShippingSettings;
use Domain\Shipment\API\USPS\Clients\Client;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\Drivers\StorePickup;
use Domain\Shipment\Drivers\UspsDriver;
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
                    username: $setting->usps_credentials['username'],
                    password: $setting->usps_credentials['password'],
                    isProduction: $setting->usps_mode,
                );
            }
        );

        $this->mergeConfigFrom(__DIR__ . '/config/shipping.php', 'shipping');
    }

    public function boot(): void
    {
        if (tenancy()->initialized) {

            $paymentMethods = ShippingMethod::all();

            if ($paymentMethods->count() > 0) {
                foreach ($paymentMethods as $courier) {
                    app(ShippingManagerInterface::class)->extend($courier->slug, function () use ($courier) {
                        return match ($courier->driver) {
                            'store-pickup' => new StorePickup(),
                            'usps' => new UspsDriver(),
                            default => throw new InvalidArgumentException(),
                        };
                    });
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
