<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Illuminate\Support\Carbon;

class ServiceOrderBillingAndDueDateData
{
    public function __construct(
        public Carbon $bill_date,
        public Carbon $due_date,
    ) {}
}
