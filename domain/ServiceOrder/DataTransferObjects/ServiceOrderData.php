<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use DateTime;

class ServiceOrderData
{
    public function __construct(
        public readonly ?int $customer_id,
        public readonly int $service_id,
        public readonly DateTime $schedule,
        public readonly ?array $additional_charges,
        public readonly ?array $data,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customer_id: (int) $data['customer_id'] ?? null,
            service_id: (int) $data['service_id'],
            schedule: $data['schedule'],
            additional_charges: $data['additional_charges'] ?? null,
            data: $data['data'] ?? null,
        );
    }
}
