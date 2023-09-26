<?php

declare(strict_types=1);

namespace Domain\Shipment\API\AustriaPost\Clients;

use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;

class AusPostRateClient extends BaseClient
{
    public static function uri(): string
    {
        return 'postage/parcel/domestic/calculate.json';
    }

    public function getRates(
        ParcelData $parcelData,
        ShippingAddressData $address
    )
    {
        
    }
}