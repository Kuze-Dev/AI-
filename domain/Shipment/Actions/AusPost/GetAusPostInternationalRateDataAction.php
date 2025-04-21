<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions\AusPost;

use Domain\Shipment\API\AusPost\Client\AusPostInternationalRateClient;
use Domain\Shipment\API\AusPost\DataTransferObjects\AusPostResponse;
use Domain\Shipment\DataTransferObjects\ParcelData;
use Domain\Shipment\DataTransferObjects\ShippingAddressData;

class GetAusPostInternationalRateDataAction
{
    public function __construct(
        private readonly AusPostInternationalRateClient $internationalRateClient,
    ) {}

    public function execute(
        ParcelData $parcelData,
        ShippingAddressData $customer_address,
    ): AusPostResponse {

        return $this->internationalRateClient->getInternationalRate(
            parcelData: $parcelData,
            address: $customer_address,
        );
    }
}
