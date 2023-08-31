<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions;

use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Customer\Models\Customer;
use Domain\Shipment\API\USPS\Contracts\RateResponse;
use Domain\Shipment\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\ShippingMethod\Models\ShippingMethod;

class GetShippingRateAction
{
    public function __construct(private readonly ShippingManagerInterface $shippingManager)
    {
    }

    public function execute(
        Customer $customer,
        ParcelData $parcelData,
        ShippingMethod $shippingMethod,
        Address $address
    ): RateResponse {

        $shippingDriver = $this->shippingManager->driver($shippingMethod->driver->value);

        if ($this->isDomesticInUnitedStates($address)) {

            return $shippingDriver->getRate(
                $customer,
                $parcelData,
                $address, // AddressValidateRequestData::formAddress($address),
                $shippingMethod,
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
            ->firstorFail();

        return $address->state->country->is($countryModel);
    }
}
