<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

use Domain\Payments\Models\Payment;
use Domain\ServiceOrder\Enums\ServiceTransactionStatus;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;

class CreateServiceTransactionData
{
    public function __construct(
        public readonly ServiceOrder $service_order,
        public readonly ?ServiceBill $service_bill,
        public readonly ?float $total_amount,
        public readonly ServiceTransactionStatus $service_transaction_status,
        public readonly Payment $payment,
    ) {
    }
}
