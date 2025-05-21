<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions;

use Domain\Shipment\API\Box\DataTransferObjects\BoxData;
use Domain\Shipment\API\Box\DataTransferObjects\BoxResponseData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\Shipment\Enums\UnitEnum;
use Domain\Shipment\Exceptions\ShippingException;
use Domain\Shipment\Models\ShippingBox;
use Domain\ShippingMethod\Enums\Driver;
use Domain\ShippingMethod\Models\ShippingMethod;

class GetBoxAction
{
    public function execute(
        ShippingMethod $shippingMethod,
        ShippingAddressData $addressDTO,
        BoxData $boxData
    ): BoxResponseData {

        /** Store Pickup */
        if ($shippingMethod->driver === Driver::STORE_PICKUP) {

            return new BoxResponseData(
                boxData: $boxData,
                dimension_units: UnitEnum::INCH->value,
                length: 0,
                width: 0,
                height: 0,
                weight: 0,
                volume: 0,
            );
        }

        /**
         * Notes:
         *
         * Check number of items in box if one
         * return the product dimension provided.
         */
        if (count($boxData->boxitems) === 1) {

            return new BoxResponseData(
                boxData: $boxData,
                dimension_units: UnitEnum::INCH->value,
                length: $boxData->boxitems[0]->length,
                width: $boxData->boxitems[0]->width,
                height: $boxData->boxitems[0]->height,
                weight: $boxData->boxitems[0]->weight,
                volume: $boxData->boxitems[0]->volume,
            );
        }

        $fit = ShippingBox::where('courier', $shippingMethod->driver)->where('volume', '>', $boxData->getTotalVolume())->orderby('volume')->first();

        if (is_null($fit)) {

            if ($shippingMethod->driver === Driver::USPS && $addressDTO->country->code !== 'US') {

                throw new ShippingException(' Unabled to ship products please choose other alternative shipping method');
            }

            return new BoxResponseData(
                boxData: $boxData,
                dimension_units: UnitEnum::INCH->value,
                length: 0,
                width: 0,
                height: 0,
                weight: $boxData->getTotalWeight(),
                volume: $boxData->getTotalVolume(),
            );
        }

        return new BoxResponseData(
            boxData: $boxData,
            dimension_units: UnitEnum::INCH->value,
            length: $fit->length,
            width: $fit->width,
            height: $fit->height,
            weight: $boxData->getTotalWeight(),
            volume: $boxData->getTotalVolume(),
        );

    }
}
