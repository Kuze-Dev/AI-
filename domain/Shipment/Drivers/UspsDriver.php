<?php

declare(strict_types=1);

namespace Domain\Shipment\Drivers;

use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Shipment\Actions\USPS\GetUSPSInternationalRateDataAction;
use Domain\Shipment\Actions\USPS\GetUSPSRateDataAction;
use Domain\Shipment\API\USPS\Contracts\RateResponse;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\DataTransferObjects\ParcelData;

class UspsDriver
{
    public function getRate(
        Customer $customer,
        ParcelData $parcelData,
        AddressValidateRequestData $addressValidateRequestData
    ): RateResponse {
        return app(GetUSPSRateDataAction::class)->execute(...func_get_args());
    }

    public function getInternationalRate(
        Customer $customer,
        ParcelData $parcelData,
        Address $address,
    ): RateResponse {
        return app(GetUSPSInternationalRateDataAction::class)->execute(
            $customer,
            $parcelData,
            $address,
        );
    }
}
