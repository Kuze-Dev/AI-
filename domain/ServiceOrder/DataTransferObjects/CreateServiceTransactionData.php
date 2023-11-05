<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceBill;

class CreateServiceTransactionData
{
    public function __construct(
        public readonly ServiceBill $service_bill,
        public readonly ServiceTransactionStatus $service_transaction_status,
        public readonly int $payment_method_id,
    ) {
    }
}
