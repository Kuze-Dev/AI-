<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Resources;

use Domain\Shipment\Actions\GetBoxAction;
use Domain\Shipment\Actions\GetShippingRateAction;
use Domain\Shipment\API\Box\DataTransferObjects\BoxData;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ReceiverData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Illuminate\Http\Request;
use TiMacDonald\JsonApi\JsonApiResource;

/**
 * @mixin \Domain\ShippingMethod\Models\ShippingMethod
 */
class ShippingMethodResourcev2 extends JsonApiResource
{
    public function toAttributes(Request $request): array
    {
        
        $shippingMethod = $this->resource;

        $reciever = ReceiverData::fromArray($request->receiver);

        $customerAddress = ShippingAddressData::fromRequestData($request->destination_address);

        #TODO: Get product list from guest cart

        $productlist = [
            [
                'product_id' => '1',
                'length' => 2,
                'width' => 2,
                'height' => 2,
                'weight' => 0.5,
            ],
        ];

        $subTotal = 10;

        $boxData = app(GetBoxAction::class)->execute(
            $shippingMethod,
            $customerAddress,
            BoxData::fromArray($productlist)
        );

       $rateData = app(GetShippingRateAction::class)
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


        return  [
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
    public function toRelationships(Request $request): array
    {
        return [
            'media' => fn () => MediaResource::collection($this->media),
        ];
    }
}
