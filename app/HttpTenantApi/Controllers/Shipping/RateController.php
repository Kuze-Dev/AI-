<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use Domain\Address\Models\Address;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\Actions\GetShippingRateAction;
use Domain\ShippingMethod\Models\ShippingMethod;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Get;

#[Middleware(['feature.tenant:'. ECommerceBase::class, 'auth:sanctum'])]
class RateController extends Controller
{
    #[Get('shipping-methods/{shippingMethod}/rate/{address}')]
    public function __invoke(ShippingMethod $shippingMethod, Address $address): mixed
    {
        $this->authorize('view', $address);

        $rateReturn = app(GetShippingRateAction::class)
            ->execute(
                parcelData: new ParcelData(
                    pounds: '10',
                    ounces: '0'
                ),
                shippingMethod: $shippingMethod,
                address: $address
            );

        return response([
            'rate' => $rateReturn->rate,
            'is_united_state_domestic' => $rateReturn->isUnitedStateDomestic,
        ]);
    }
}
