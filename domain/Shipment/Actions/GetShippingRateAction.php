<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions;

use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\API\USPS\DataTransferObjects\AddressValidateRequestData;
use Domain\Shipment\Drivers\UspsDriver;

class GetShippingRateAction
{
    public function execute(ParcelData $parcelData, string $slug)
    {
        $usps = new UspsDriver();

        dd($parcelData->toArray());

        dd($usps->getRate($parcelData->toArray(), AddressValidateRequestData::fromArray([
            'Address1' => 'STE K',
            'Address2' => '185 Berry Street',
            'City' => 'San Francisco',
            'State' => 'CA',
            'Zip5' => '5656',
            'Zip4' => '2342',
        ])));
    }
}
