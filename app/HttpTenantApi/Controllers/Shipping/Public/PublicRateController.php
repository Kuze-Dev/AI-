<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping\Public;

use App\Features\ECommerce\ECommerceBase;
use App\HttpTenantApi\Requests\Shipping\ShippingRateRequest;
use Domain\Cart\Actions\PublicCart\GuestCartSummaryAction;
use Domain\Shipment\Actions\GetBoxAction;
use Domain\Shipment\Actions\GetShippingRateAction;
use Domain\Shipment\API\Box\DataTransferObjects\BoxData;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\Shipment\Enums\UnitEnum;
use Domain\ShippingMethod\Models\ShippingMethod;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Post;
use Throwable;

#[Middleware(['feature.tenant:'.ECommerceBase::class])]
class PublicRateController
{
    /** @throws Throwable */
    #[POST('shipping-rates', name: 'shipping-rates')]
    public function shippingRate(ShippingRateRequest $request): mixed
    {
        /** @var ShippingMethod */
        $shippingMethod = ShippingMethod::where('slug', $request->courier)->firstOrFail();

        try {

            if (! $shippingMethod->active) {
                abort(404);
            }

            $cartLines = $request->getCartLines();

            $productlist = app(GuestCartSummaryAction::class)->getProducts($cartLines, UnitEnum::INCH);

            $subTotal = app(GuestCartSummaryAction::class)->getSubTotal($cartLines);

            $customerAddress = $request->toShippingAddressDto();

            $boxData = app(GetBoxAction::class)->execute(
                $shippingMethod,
                $customerAddress,
                BoxData::fromArray($productlist)
            );

            return response(
                app(GetShippingRateAction::class)
                    ->execute(
                        parcelData: new ParcelData(
                            reciever: $request->toRecieverDTO(),
                            pounds: (string) $boxData->weight,
                            ounces: '0',
                            zip_origin: $shippingMethod->shipper_zipcode,
                            parcel_value: (string) $subTotal,
                            height: (string) $boxData->height,
                            width: (string) $boxData->width,
                            length: (string) $boxData->length,
                            boxData: $boxData->boxData,
                            ship_from_address: new ShippingAddressData(
                                address: $shippingMethod->shipper_address,
                                city: $shippingMethod->shipper_city,
                                state: $shippingMethod->state,
                                zipcode: $shippingMethod->shipper_zipcode,
                                country: $shippingMethod->country,
                                code: $shippingMethod->country->code,
                            ),
                        ),
                        shippingMethod: $shippingMethod,
                        address: $customerAddress
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
