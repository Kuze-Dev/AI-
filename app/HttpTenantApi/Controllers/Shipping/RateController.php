<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use Domain\Address\Models\Address;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\Actions\GetShippingRateAction;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Get;

#[Middleware(['feature.tenant:' . ECommerceBase::class, 'auth:sanctum'])]
class RateController extends Controller
{
    #[Get('shipping-methods/{shippingMethod}/rate/{address}')]
    public function __invoke(ShippingMethod $shippingMethod, Address $address): mixed
    {
        if ( ! $shippingMethod->status) {
            abort(404);
        }

        $this->authorize('view', $address);

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Auth::user();

        return response(
            app(GetShippingRateAction::class)
                ->execute(
                    customer: $customer->load('verifiedAddress'),
                    parcelData: new ParcelData(
                        pounds: '10',
                        ounces: '0',
                        zip_origin: $shippingMethod->ship_from_address['zip5'],
                        parcel_value: '200',
                        height: '10',
                        width: '10',
                        length: '10',
                    ),
                    shippingMethod: $shippingMethod,
                    address: $address
                )->getRateResponseAPI()
        );
    }
}
