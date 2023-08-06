<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Shipping;

use App\Features\ECommerce\ECommerceBase;
use App\Http\Controllers\Controller;
use Domain\Address\Models\Address;
use Domain\Cart\Actions\CartSummaryAction;
use Domain\Cart\Models\CartLine;
use Domain\Cart\Requests\CartSummaryRequest;
use Domain\Shipment\Actions\GetBoxAction;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\Actions\GetShippingRateAction;
use Domain\Shipment\API\Box\DataTransferObjects\BoxData;
use Domain\Shipment\DataTransferObjects\ShipFromAddressData;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Support\Facades\Auth;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Get;

#[Middleware(['feature.tenant:' . ECommerceBase::class, 'auth:sanctum'])]
class RateController extends Controller
{
    #[Get('shipping-methods/{shippingMethod}/rate/{address}')]
    public function __invoke(ShippingMethod $shippingMethod, Address $address, CartSummaryRequest $request): mixed
    {
        if ( ! $shippingMethod->active) {
            abort(404);
        }

        $this->authorize('view', $address);

        $validated = $request->validated();

        $cartLineIds = explode(',', $validated['cart_line_ids']);

        /** @var \Domain\Customer\Models\Customer $customer */
        $customer = Auth::user();

        $cartLines = CartLine::query()
            ->with('purchasable')
            ->whereHas('cart', function ($query) use ($customer) {
                $query->whereBelongsTo($customer);
            })
            ->whereNull('checked_out_at')
            ->whereIn((new CartLine())->getRouteKeyName(), $cartLineIds)
            ->get();

        $productlist = app(CartSummaryAction::class)->getProducts($cartLines);

        $boxData = app(GetBoxAction::class)->execute(
            $shippingMethod,
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
    }
}
