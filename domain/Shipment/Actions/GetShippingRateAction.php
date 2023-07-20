<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions;

use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Customer\Models\Customer;
use Domain\Shipment\API\USPS\Contracts\RateResponse;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\ShippingMethod\Models\ShippingMethod;

class GetShippingRateAction
{
    public function execute(
        Customer $customer,
        ParcelData $parcelData,
        ShippingMethod $shippingMethod,
        Address $address
    ): RateResponse {

        $shippingDriver = $shippingMethod->driver->getShipping();

        if ($this->isDomesticInUnitedStates($address)) {
            return $shippingDriver->getRate(
                $customer,
                $parcelData,
                AddressValidateRequestData::formAddress($address)
            );
        }

        return $shippingDriver->getInternationalRate(
            $customer,
            $parcelData,
            $address,
        );
    }

    protected function isDomesticInUnitedStates(Address $address): bool
    {
        $countryModel = Country::where([
            'name' => 'United States', // TODO: handle this properly
            'code' => 'US',
        ])
            ->first();

        return $address->state->country->is($countryModel);
    }
}
