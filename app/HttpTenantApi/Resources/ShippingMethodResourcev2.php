<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Address\Models\Address;
use Domain\Cart\Actions\CartSummaryAction;
use Domain\Cart\Enums\CartUserType;
use Domain\Cart\Helpers\PrivateCart\CartLineQuery;
use Domain\Shipment\Actions\GetBoxAction;
use Domain\Shipment\Actions\GetShippingRateAction;
use Domain\Shipment\API\Box\DataTransferObjects\BoxData;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ReceiverData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\Shipment\Enums\UnitEnum;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Throwable;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\ShippingMethod\Models\ShippingMethod
 */
class ShippingMethodResourcev2 extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        $rateData = $this->getRateData($request);

        return [
            'name' => $this->title,
            'slug' => $this->slug,
            'subtitle' => $this->subtitle,
            'description' => $this->description,
            'driver' => $this->driver,
            'status' => $this->active,
            'rate' => $rateData,

        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'media' => fn () => MediaResource::collection($this->media),
        ];
    }

    private function getRateData(Request $request): array
    {

        try {

            // TODO: handle when user is authenticated.

            $shippingMethod = $this->resource;
            /** @var \Domain\Customer\Models\Customer|null */
            $user = auth()->user();

            if ($user) {
                /** @var \Domain\Address\Models\Address */
                $addressModel = Address::findOrfail($request->address_id);

                $reciever = ReceiverData::fromCustomerModel($user);

                $customerAddress = ShippingAddressData::fromAddressModel($addressModel);

            } else {
                $reciever = ReceiverData::fromArray($request->receiver);

                $customerAddress = ShippingAddressData::fromRequestData($request->destination_address);
            }

            $cartLines = $this->getCartLines($request);

            $productlist = app(CartSummaryAction::class)->getProducts($cartLines, UnitEnum::INCH);

            $subTotal = app(CartSummaryAction::class)->getSubTotal($cartLines);

            $boxData = app(GetBoxAction::class)->execute(
                $shippingMethod,
                $customerAddress,
                BoxData::fromArray($productlist)
            );

            return app(GetShippingRateAction::class)
                ->execute(
                    parcelData: new ParcelData(
                        reciever: $reciever,
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
                )->getRateResponseAPI();
        } catch (Throwable $th) {

            return [
                'message' => $th->getMessage(),
            ];
        }
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, \Domain\Cart\Models\CartLine> */
    private function getCartLines(Request $request): Collection
    {

        $cartLineIds = is_array($request->cart_line_ids) ? $request->cart_line_ids : explode(',', (string) $request->cart_line_ids);

        $type = auth()->user() ? CartUserType::AUTHENTICATED : CartUserType::GUEST;

        /** @var string $sessionId */
        $sessionId = auth()->user() ? auth()->user()->id : $request->bearerToken();

        if ($type === CartUserType::AUTHENTICATED) {
            $cartLines = app(CartLineQuery::class)->execute($cartLineIds);
        } else {
            $cartLines = app(CartLineQuery::class)->guests($cartLineIds, $sessionId);
        }

        return $cartLines;
    }
}
