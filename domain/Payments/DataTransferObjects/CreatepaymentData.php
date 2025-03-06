<?php

declare(strict_types=1);

namespace Domain\Payments\DataTransferObjects;

class CreatepaymentData
{
    public function __construct(
        public readonly TransactionData $transactionData,
        public readonly string $payment_driver,
    ) {}
}
