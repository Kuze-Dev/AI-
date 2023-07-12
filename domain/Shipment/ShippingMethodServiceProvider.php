<?php

declare(strict_types=1);

namespace Domain\Shipment;

use App\Settings\ShippingSettings;
use Domain\Shipment\API\USPS\Connection;
use Domain\Shipment\Contracts\ShippingManagerInterface;
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

        $this->app->singleton(
            Connection::class,
            function () {
                $setting = app(ShippingSettings::class);

                return new Connection(
                    username: $setting->usps_credentials['username'],
                    password: $setting->usps_credentials['password'],
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
                            // 'store-pickup' => new PaypalProvider(),
                            'usps' => new UspsDriver(),
                            // 'ups' => new OfflinePayment(),
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
            Connection::class,
        ];
    }
}
