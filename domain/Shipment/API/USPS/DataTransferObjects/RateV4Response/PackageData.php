<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects\RateV4Response;

class PackageData
{
    public function __construct(
        public readonly int $zip_origination,
        public readonly int $zip_destination,
        public readonly int $pounds,
        public readonly int $qunces,
        public readonly string $container,
        public readonly int $zone,
        public readonly PostageData $postage
    ) {}
}
