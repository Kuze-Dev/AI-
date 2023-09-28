<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Shipment\Actions\AusPost\GetAusPostInternationalRateDataAction;
use Domain\Shipment\Actions\AusPost\GetAuspostRateDataAction;
use Domain\Shipment\Contracts\API\RateResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\ShippingMethod\Models\ShippingMethod;

class AusPostDriver
{
    public function getRate(
        ParcelData $parcelData,
        ShippingAddressData $address,
        ShippingMethod $shippingMethod
    ): RateResponse {

        return app(GetAuspostRateDataAction::class)->execute(
            $parcelData,
            $address
        );
    }

    public function getInternationalRate(
        ParcelData $parcelData,
        ShippingAddressData $address,
    ): RateResponse {

        return app(GetAusPostInternationalRateDataAction::class)->execute(
            $parcelData,
            $address
        );
    }
}
