<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\ECommerce\ECommerceBase;
use Domain\Address\Models\Address;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\Actions\GetShippingRateAction;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Get;

#[Middleware(['feature.tenant:'. ECommerceBase::class, 'auth:sanctum'])]
class RateController
{
    #[Get('/shipping/{driver}/rate/{address}')]
    public function __invoke(string $driver, Address $address): mixed
    {
        // TODO: validate $driver
        $rateReturn = app(GetShippingRateAction::class)
            ->execute(
                parcelData: new ParcelData(
                    pounds: '10',
                    ounces: '0'
                ),
                address: $address,
                driver: $driver
            );

        return response([
            'rate' => $rateReturn->rate,
            'is_united_state_domestic' => $rateReturn->isUnitedStateDomestic,
        ]);
    }
}
