<?php

declare(strict_types=1);

namespace Domain\Payments\DataTransferObjects;

use Domain\Payments\Models\Payment;

class ProviderData
{
    public function __construct(
        public readonly TransactionData $transactionData,
        public readonly Payment $paymentModel,
        public readonly int $payment_method_id,
    ) {}
}
