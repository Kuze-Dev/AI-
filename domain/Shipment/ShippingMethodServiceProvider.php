<?php

declare(strict_types=1);

namespace Domain\Payments;

use Domain\Payments\Providers\OfflinePayment;
use Domain\Payments\Providers\PaypalProvider;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\Drivers\UspsDriver;
use Domain\Shipment\ShippingManager;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class ShippingMethodServiceProvider extends ServiceProvider implements DeferrableProvider
{
    public function register()
    {
        $this->app->singleton(
            ShippingManagerInterface::class,
            fn ($app) => $app->make(ShippingManager::class)
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

    public function provides()
    {

        return [
            ShippingManagerInterface::class,
        ];
    }
}
