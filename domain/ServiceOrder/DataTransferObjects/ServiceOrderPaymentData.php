<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;

class ServiceOrderPaymentData
{
    public function __construct(
        public readonly ServiceTransactionStatus $service_transaction_status,
        public readonly ServiceBillStatus $service_bill_status,
    ) {}
}
