<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use Domain\Address\Models\Address;
use Domain\Cart\Actions\CartSummaryAction;
use Domain\Cart\Requests\CartSummaryRequest;
use Domain\Shipment\Actions\GetBoxAction;
use Domain\Shipment\Actions\GetShippingRateAction;
use Domain\Shipment\API\Box\DataTransferObjects\BoxData;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ReceiverData;
// use Domain\Shipment\DataTransferObjects\ShipFromAddressData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\Shipment\Enums\UnitEnum;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Throwable;

#[Middleware(['feature.tenant:'.ECommerceBase::class, 'auth:sanctum'])]
class RateController extends Controller
{
    #[Get('shipping-methods/{shippingMethod}/rate/{address}')]
    public function __invoke(ShippingMethod $shippingMethod, Address $address, CartSummaryRequest $request): mixed
    {

        try {
            if (! $shippingMethod->active) {
                abort(404);
            }

            $this->authorize('view', $address);

            /** @var \Domain\Customer\Models\Customer $customer */
            $customer = Auth::user();

            $cartLines = $request->getCartLines();

            $productlist = app(CartSummaryAction::class)->getProducts($cartLines, UnitEnum::INCH);

            $subTotal = app(CartSummaryAction::class)->getSubTotal($cartLines);

            $customerAddress = ShippingAddressData::fromAddressModel($address);

            $boxData = app(GetBoxAction::class)->execute(
                $shippingMethod,
                $customerAddress,
                BoxData::fromArray($productlist)
            );

            return response(
                app(GetShippingRateAction::class)
                    ->execute(
                        parcelData: new ParcelData(
                            reciever: ReceiverData::fromCustomerModel($customer->load('verifiedAddress')),
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
