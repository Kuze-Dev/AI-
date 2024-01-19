<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\ServiceOrder\Models\ServiceOrder;

class ServiceBillMilestonePipelineData
{
    public function __construct(
        public readonly ServiceOrder $service_order,
        public readonly array $payment_plan,
    ) {
    }
}
