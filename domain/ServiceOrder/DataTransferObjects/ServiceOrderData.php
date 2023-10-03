<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use DateTime;

class ServiceOrderData
{
    public function __construct(
        public readonly ?int $customerId,
        public readonly int $serviceId,
        public readonly DateTime $schedule,
        public readonly ?int $serviceAddressId,
        public readonly ?int $billingAddressId,
        public readonly bool $isSameAsBilling,
        public readonly ?array $additionalCharges,
        public readonly ?array $form,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            customerId: (int) $data['customer_id'] ?? null,
            serviceId: (int) $data['service_id'],
            schedule: new DateTime($data['schedule']),
            serviceAddressId: (int) $data['service_address_id'],
            billingAddressId: (int) $data['billing_address_id'],
            isSameAsBilling: $data['is_same_as_billing'],
            additionalCharges: $data['additional_charges'] ?? null,
            form: $data['data'] ?? null,
        );
    }
}
