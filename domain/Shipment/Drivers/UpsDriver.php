<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Shipment\Actions\UPS\GetUPSInternationalRateDataAction;
use Domain\Shipment\Actions\UPS\GetUPSRateDataAction;
use Domain\Shipment\Contracts\API\RateResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\ShippingMethod\Models\ShippingMethod;

class UpsDriver
{
    public function getRate(
        ParcelData $parcelData,
        ShippingAddressData $address,
        ShippingMethod $shippingMethod
    ): RateResponse {

        return app(GetUPSRateDataAction::class)->execute(
            $parcelData,
            $address
        );
    }

    public function getInternationalRate(
        ParcelData $parcelData,
        ShippingAddressData $address,
    ): RateResponse {
        return app(GetUPSInternationalRateDataAction::class)->execute(
            $parcelData,
            $address,
        );
    }
}
