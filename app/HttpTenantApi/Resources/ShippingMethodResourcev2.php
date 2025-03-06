<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Address\Models\Address;
use Domain\Cart\Actions\CartSummaryAction;
use Domain\Cart\Enums\CartUserType;
use Domain\Cart\Helpers\PrivateCart\CartLineQuery;
use Domain\Customer\Models\Customer;
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
 * @property-read  \Domain\ShippingMethod\Models\ShippingMethod $resource
 */
class ShippingMethodResourcev2 extends JsonApiResource
{
    #[\Override]
    public function toAttributes(Request $request): array
    {
        $rateData = $this->getRateData($request);

        return [
            'name' => $this->resource->title,
            'slug' => $this->resource->slug,
            'subtitle' => $this->resource->subtitle,
            'description' => $this->resource->description,
            'driver' => $this->resource->driver,
            'status' => $this->resource->active,
            'rate' => $rateData,

        ];
    }

    /** @return array<string, callable> */
    #[\Override]
    public function toRelationships(Request $request): array
    {
        return [
            'media' => fn () => MediaResource::collection($this->resource->media),
        ];
    }

    private function getRateData(Request $request): array
    {

        try {

            // TODO: handle when user is authenticated.

            $shippingMethod = $this->resource;
            /** @var \Domain\Customer\Models\Customer|null $user */
            $user = auth()->user();

            if ($user) {
                /** @var \Domain\Address\Models\Address $addressModel */
                $addressModel = Address::findOrfail($request->address_id);

                $reciever = ReceiverData::fromCustomerModel($user);

                $customerAddress = ShippingAddressData::fromAddressModel($addressModel);

            } else {
                $reciever = ReceiverData::fromArray($request->receiver);

                $customerAddress = ShippingAddressData::fromRequestData($request->destination_address);
            }

            $cartLines = $this->getCartLines($request);

            $productList = app(CartSummaryAction::class)->getProducts($cartLines, UnitEnum::INCH);

            $subTotal = app(CartSummaryAction::class)->getSubTotal($cartLines);

            $boxData = app(GetBoxAction::class)->execute(
                $shippingMethod,
                $customerAddress,
                BoxData::fromArray($productList)
            );

            return app(GetShippingRateAction::class)
                ->execute(
                    parcelData: new ParcelData(
                        ship_from_address: new ShippingAddressData(
                            address: $shippingMethod->shipper_address,
                            city: $shippingMethod->shipper_city,
                            zipcode: $shippingMethod->shipper_zipcode,
                            code: $shippingMethod->country->code,
                            state: $shippingMethod->state,
                            country: $shippingMethod->country,
                        ),
                        reciever: $reciever,
                        pounds: (string) $boxData->weight,
                        ounces: '0',
                        zip_origin: $shippingMethod->shipper_zipcode,
                        boxData: $boxData->boxData,
                        parcel_value: (string) $subTotal,
                        height: (string) $boxData->height,
                        width: (string) $boxData->width,
                        length: (string) $boxData->length,
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

        /** @var Customer|null $customer */
        $customer = auth()->user();

        /** @var string|int $sessionId */
        $sessionId = $customer?->id ?? $request->bearerToken();

        if ($type === CartUserType::AUTHENTICATED) {
            $cartLines = app(CartLineQuery::class)->execute($cartLineIds);
        } else {
            $cartLines = app(CartLineQuery::class)->guests($cartLineIds, (string) $sessionId);
        }

        return $cartLines;
    }
}
