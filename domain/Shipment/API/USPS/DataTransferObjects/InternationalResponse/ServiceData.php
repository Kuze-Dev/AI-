<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects\InternationalResponse;

class ServiceData
{
    /** @param  array<\Domain\Shipment\API\USPS\DataTransferObjects\InternationalResponse\ExtraServiceData>  $extra_services */
    public function __construct(
        public readonly int $id,
        public readonly float $pound,
        public readonly int $qunces,
        public readonly string $mail_type,
        public readonly int $width,
        public readonly int $length,
        public readonly int $height,
        public readonly string $country,
        public readonly float $postage,
        public readonly array $extra_services,
        public readonly float $value_of_content,
        public readonly string $svc_commitment,
        public readonly string $svc_description,
        public readonly string $max_dimension,
        public readonly int $max_weight,
    ) {}
}
