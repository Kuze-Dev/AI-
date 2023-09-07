<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions\UPS;

use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Shipment\API\UPS\Clients\UPSRateClient;
use Domain\Shipment\API\UPS\DataTransferObjects\UpsResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;

class GetUPSInternationalRateDataAction
{
    public function __construct(
        private readonly UPSRateClient $rateClient,
    ) {
    }

    public function execute(
        Customer $customer,
        ParcelData $parcelData,
        Address $address
    ): UpsResponse {

        return $this->rateClient->getInternationalRate(
            customer: $customer,
            parcelData: $parcelData,
            address: $address,
        );
    }
}
