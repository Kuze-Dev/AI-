<?php

declare(strict_types=1);

namespace Domain\Shipment;

use App\Settings\ShippingSettings;
use Domain\Shipment\API\USPS\Clients\Client;
use Illuminate\Support\ServiceProvider;

class ShippingMethodServiceProvider extends ServiceProvider
{
    public function register(): void
    {

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

    //    public function boot(): void
    //    {
    //    }
}
