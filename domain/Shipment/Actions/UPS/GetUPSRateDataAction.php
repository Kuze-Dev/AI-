<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions\UPS;

use Domain\Shipment\API\UPS\Clients\UPSRateClient;
use Domain\Shipment\API\UPS\DataTransferObjects\UpsResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;

class GetUPSRateDataAction
{
    public function __construct(
        private readonly UPSRateClient $rateClient,
    ) {}

    public function execute(
        ParcelData $parcelData,
        ShippingAddressData $customer_address,
    ): UpsResponse {

        return $this->rateClient->getRate(
            parcelData: $parcelData,
            address: $customer_address,
        );
    }
}
