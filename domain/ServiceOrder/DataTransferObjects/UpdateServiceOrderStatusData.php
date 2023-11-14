<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;

class UpdateServiceOrderStatusData
{
    public function __construct(
        public readonly ServiceOrderStatus $service_order_status,
    ) {
    }
}
