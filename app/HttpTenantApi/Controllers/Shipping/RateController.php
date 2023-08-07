<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use Domain\Address\Models\Address;
use Domain\Shipment\Actions\GetBoxAction;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\Actions\GetShippingRateAction;
use Domain\Shipment\API\Box\DataTransferObjects\BoxData;
use Domain\Shipment\DataTransferObjects\ShipFromAddressData;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Get;
use Throwable;

#[Middleware(['feature.tenant:'. ECommerceBase::class, 'auth:sanctum'])]
class RateController extends Controller
{
    #[Get('shipping-methods/{shippingMethod}/rate/{address}')]
    public function __invoke(ShippingMethod $shippingMethod, Address $address): mixed
    {

        try {
            if ( ! $shippingMethod->active) {
                abort(404);
            }

            $this->authorize('view', $address);

            /** @var \Domain\Customer\Models\Customer $customer */
            $customer = Auth::user();

            /**
             * NOTES:
             *  unit for product dimencion is inch
             *  L" x W" x H" = V "^3
             *
             * unit for weight is LBS
             */

            /**
             * Test Data
             *
             * @var array */
            $productlist = [
                ['product_id' => '1', 'length' => 10, 'width' => 5, 'height' => 0.3, 'weight' => 0.18],
                ['product_id' => '1', 'length' => 10, 'width' => 5, 'height' => 0.3, 'weight' => 0.18],
                // ['product_id' => '1', 'length' => 10, 'width' => 5, 'height' => 0.3, 'weight' => 0.18],
            ];

            $boxData = app(GetBoxAction::class)->execute(
                $shippingMethod,
                $address,
                BoxData::fromArray($productlist)
            );

            return response(
                app(GetShippingRateAction::class)
                    ->execute(
                        customer: $customer->load('verifiedAddress'),
                        parcelData: new ParcelData(
                            pounds: (string) $boxData->weight,
                            ounces: '0',
                            zip_origin: $shippingMethod->shipper_zipcode,
                            parcel_value: '200',
                            height: (string) $boxData->height,
                            width: (string) $boxData->width,
                            length: (string) $boxData->length,
                            boxData: $boxData->boxData,
                            ship_from_address: new ShipFromAddressData(
                                address: $shippingMethod->shipper_address,
                                city: $shippingMethod->shipper_city,
                                state: $shippingMethod->state,
                                zipcode: $shippingMethod->shipper_zipcode,
                                country: $shippingMethod->country,
                                code: $shippingMethod->country->code,
                            ),
                        ),
                        shippingMethod: $shippingMethod,
                        address: $address
                    )->getRateResponseAPI()
            );
        } catch (Throwable $th) {

            return response()->json(
                [
                    'message' => $th->getMessage(),
                ],
                422
            );
        }

    }
}
