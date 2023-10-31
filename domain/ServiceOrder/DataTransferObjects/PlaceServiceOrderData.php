<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

class PlaceServiceOrderData
{
    public function __construct(
        public readonly int|string $customer_id,
        public readonly int|string $service_id,
        public readonly string $schedule,
        public readonly int|string|null $service_address_id,
        public readonly int|string|null $billing_address_id,
        public readonly bool $is_same_as_billing,
        public readonly ?array $additional_charges,
        public readonly ?array $form,
    ) {
    }
}
