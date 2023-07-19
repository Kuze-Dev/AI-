<?php

declare(strict_types=1);

namespace Domain\Shipment\Actions\USPS;

use Domain\Shipment\API\USPS\Clients\RateClient;
use Domain\Shipment\API\USPS\DataTransferObjects\InternationalResponse\IntlRateV2ResponseData;

class GetUSPSInternationalRateAction
{
    public function __construct(
        private readonly RateClient $rateClient,
    ) {
    }

    public function execute(): IntlRateV2ResponseData
    {
        return $this->rateClient->getInternationalVersion2();
    }
}
