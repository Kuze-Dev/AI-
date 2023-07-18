<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions;

use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\DataTransferObjects\ShippingRateActionReturn;
use Domain\ShippingMethod\Models\ShippingMethod;

class GetShippingRateAction
{
    public function __construct(private readonly ShippingManagerInterface $shippingManager)
    {
    }

    public function execute(
        ParcelData $parcelData,
        ShippingMethod $shippingMethod,
        Address $address
    ): ShippingRateActionReturn {
        $shippingDriver = $this->shippingManager->driver($shippingMethod->driver->value);

        if ($this->isDomesticInUnitedStates($address)) {
            return new ShippingRateActionReturn(
                rate: $shippingDriver->getRate(
                    $parcelData->toArray(),
                    AddressValidateRequestData::formAddress($address)
                ),
                isUnitedStateDomestic: true
            );
        }

        return new ShippingRateActionReturn(
            rate: $shippingDriver->getInternationalRate(),
            isUnitedStateDomestic: false
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
