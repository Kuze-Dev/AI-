<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions;

use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\Contracts\ShippingManagerInterface;

class GetShippingRateAction
{
    public function execute(
        ParcelData $parcelData,
        Address $customerShippingAddress,
        string $slug
    ): float {

        $shipping = app(ShippingManagerInterface::class)->driver($slug);

        if ($this->isDomestic($customerShippingAddress)) {

            //handle us address

            $rate = $shipping->getRate($parcelData->toArray(), AddressValidateRequestData::fromArray([
                'Address1' => '',
                'Address2' => $customerShippingAddress->address_line_1,
                'City' => $customerShippingAddress->city,
                'State' => $customerShippingAddress->state->name,
                'Zip5' => $customerShippingAddress->zip_code,
                'Zip4' => '',
            ]));

        } else {

            $rate = $shipping->getInternationalRate();

        }

        $rate = $shipping->getInternationalRate();

        return $rate;
    }

    /** #checking if address is in United States */
    protected function isDomestic(Address $customerShippingAddress): bool
    {

        $countryModel = Country::where('name', 'United States')->where('code', 'US')->first();

        return $customerShippingAddress->state->country_id == $countryModel->id;
    }
}
