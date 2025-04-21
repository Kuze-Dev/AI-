<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects\InternationalResponse;

class PackageData
{
    /** @param  \Domain\Shipment\API\USPS\DataTransferObjects\InternationalResponse\ServiceData[]  $services */
    public function __construct(
        public readonly string $prohibition,
        public readonly string $restriction,
        public readonly string $observation,
        public readonly string $customs_form,
        public readonly string $express_mail,
        public readonly string $areas_served,
        public readonly string $additional_restriction,
        public readonly array $services,
    ) {}
}
