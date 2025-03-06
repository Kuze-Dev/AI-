<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions;

use Domain\Shipment\Contracts\API\RateResponse;
use Domain\Shipment\Contracts\ShippingManagerInterface;
use Domain\Shipment\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\ShippingMethod\Enums\Driver;
use Domain\ShippingMethod\Models\ShippingMethod;

class GetShippingRateAction
{
    public function __construct(private readonly ShippingManagerInterface $shippingManager) {}

    public function execute(
        ParcelData $parcelData,
        ShippingMethod $shippingMethod,
        ShippingAddressData $address
    ): RateResponse {

        $shippingDriver = $this->shippingManager->driver($shippingMethod->driver->value);

        if ($shippingMethod->driver === Driver::AUSPOST && $this->isDomesticShipping($address, 'AU')) {

            return $shippingDriver->getRate(
                $parcelData,
                $address,
                $shippingMethod,
            );
        }

        if ($this->isDomesticInUnitedStates($address) &&
            in_array($shippingMethod->driver, [Driver::USPS, Driver::UPS], true)
        ) {

            return $shippingDriver->getRate(
                $parcelData,
                $address, // AddressValidateRequestData::formAddress($address),
                $shippingMethod,
            );
        }

        return $shippingDriver->getInternationalRate(
            $parcelData,
            $address,
        );
    }

    protected function isDomesticInUnitedStates(ShippingAddressData $address): bool
    {
        return $address->country->code === 'US';
    }

    protected function isDomesticShipping(ShippingAddressData $address, string $countryCode): bool
    {
        return $address->country->code === $countryCode;
    }
}
