<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects\RateV4Response;

class PostageData
{
    public function __construct(
        public readonly string $mail_service,
        public readonly float $rate
    ) {}
}
