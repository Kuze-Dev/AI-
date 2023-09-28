<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions\AusPost;

use Domain\Customer\Models\Customer;
use Domain\Shipment\API\AusPost\Client\AusPostRateClient;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;

class GetAusPostInternationalRateDataAction
{
    public function __construct(
        private readonly AusPostRateClient $rateClient,
    ) {
    }

    public function execute(
        Customer $customer,
        ParcelData $parcelData,
        ShippingAddressData $customer_address,
    ) {

        return $this->rateClient->getInternationalRate(
            customer: $customer,
            parcelData: $parcelData,
            address: $customer_address,
        );
    }
}
