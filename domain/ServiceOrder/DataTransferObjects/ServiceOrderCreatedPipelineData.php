<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\ServiceOrder\Models\ServiceOrder;

class ServiceOrderCreatedPipelineData
{
    public function __construct(
        public readonly ServiceOrder $serviceOrder,
        public readonly int|string|null $service_address_id,
        public readonly int|string|null $billing_address_id,
        public readonly bool $is_same_as_billing,
    ) {}
}
