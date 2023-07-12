<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

use Domain\Shipment\API\USPS\Constants\Container;
use Domain\Shipment\API\USPS\Constants\ServiceType;

class RateV4RequestData
{
    public function __construct(
        public readonly string $Service,
        public readonly string $ZipOrigination,
        public readonly string $ZipDestination,
        public readonly string $Pounds,
        public readonly string $Ounces,
        public readonly string $Container,
        public readonly bool $Machinable = true,
    ) {
    }

    public static function fromArray(array $data): self
    {

        return new self(
            Service: $data['service'] ?? ServiceType::SERVICE_PRIORITY,
            ZipOrigination: $data['zipOrigination'],
            ZipDestination: $data['zipDestination'],
            Pounds: $data['pounds'],
            Ounces: $data['ounces'],
            Container: $data['container'] ?? Container::CONTAINER_VARIABLE,
        );
    }
}
