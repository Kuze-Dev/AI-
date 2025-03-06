<?php

declare(strict_types=1);

namespace Domain\Shipment\API\USPS\DataTransferObjects\InternationalResponse;

class ExtraServiceData
{
    public function __construct(
        public readonly int $service_id,
        public readonly string $service_name,
        public readonly bool $available,
        public readonly float $price,
        public readonly bool $declared_value_required,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            service_id: (int) $data['ServiceID'],
            service_name: $data['ServiceName'],
            available: $data['Available'] === 'True',
            price: (float) $data['Price'],
            declared_value_required: ($data['DeclaredValueRequired'] ?? null) === 'True',
        );
    }
}
