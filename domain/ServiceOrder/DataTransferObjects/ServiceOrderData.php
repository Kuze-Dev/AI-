<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use DateTime;

class ServiceOrderData
{
    public function __construct(
        public readonly int $customer_id,
        public readonly int $service_id,
        public readonly DateTime $schedule,
        public readonly ?int $service_address_id,
        public readonly ?int $billing_address_id,
        public readonly bool $is_same_as_billing,
        public readonly ?array $additional_charges,
        public readonly ?array $form,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customer_id: (int) $data['customer_id'],
            service_id: (int) $data['service_id'],
            schedule: new DateTime($data['schedule']),
            service_address_id: (int) $data['service_address_id'],
            billing_address_id: (int) $data['billing_address_id'],
            is_same_as_billing: $data['is_same_as_billing'],
            additional_charges: $data['additional_charges'] ?? null,
            form: $data['data'] ?? null,
        );
    }
}
