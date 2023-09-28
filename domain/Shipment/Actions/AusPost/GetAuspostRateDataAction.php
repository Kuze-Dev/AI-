<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions\AusPost;

use Domain\Shipment\API\AusPost\Client\AusPostRateClient;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;
use Domain\Shipment\API\AusPost\DataTransferObjects\AusPostResponse;

class GetAuspostRateDataAction
{
    public function __construct(
        private readonly AusPostRateClient $rateClient,
    ) {
    }

    public function execute(
        ParcelData $parcelData,
        ShippingAddressData $customer_address,
    ): AusPostResponse {

        return $this->rateClient->getRate(
            parcelData: $parcelData,
            address: $customer_address,
        );
    }
}
