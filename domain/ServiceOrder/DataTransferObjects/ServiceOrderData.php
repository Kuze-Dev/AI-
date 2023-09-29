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
        public readonly ?string $serviceAddress,
        public readonly ?array $additionalCharges,
        public readonly ?array $form,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customer_id: (int) $data['customer_id'] ?? null,
            service_id: (int) $data['service_id'],
            schedule: new DateTime($data['schedule']),
            serviceAddress: $data['service_address'],
            additionalCharges: $data['additional_charges'] ?? null,
            form: $data['data'] ?? null,
        );
    }
}
