<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\Actions;

use App\Features\Shopconfiguration\Shipping\ShippingAusPost;
use App\Features\Shopconfiguration\Shipping\ShippingStorePickup;
use App\Features\Shopconfiguration\Shipping\ShippingUps;
use App\Features\Shopconfiguration\Shipping\ShippingUsps;
use Domain\ShippingMethod\Enums\Driver;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Support\Str;

class GetAvailableShippingDriverAction
{
    public function execute(): array
    {
        return array_filter(
            collect(Driver::cases())
                ->mapWithKeys(
                    function (Driver $target) {

                        $shippingDriver = match ($target) {
                            Driver::USPS => ShippingUsps::class,
                            Driver::UPS => ShippingUps::class,
                            Driver::STORE_PICKUP => ShippingStorePickup::class,
                            Driver::AUSPOST => ShippingAusPost::class,
                        };

                        return [
                            $target->value => TenantFeatureSupport::active($shippingDriver) ?
                             Str::of($target->value)->headline()->upper() : false,
                        ];
                    }
                )
                ->toArray(),
            fn ($value) => $value !== false
        );
    }
}
