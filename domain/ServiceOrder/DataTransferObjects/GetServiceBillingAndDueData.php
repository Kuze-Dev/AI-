<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Models\ServiceTransaction;

class GetServiceBillingAndDueData
{
    public function __construct(
        public readonly ServiceOrder $service_order,
        public readonly ServiceBill $service_bill,
        public readonly ServiceTransaction $service_transaction
    ) {}
}
