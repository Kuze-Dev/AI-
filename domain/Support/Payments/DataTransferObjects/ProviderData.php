<?php

declare(strict_types=1);

namespace Domain\Support\Payments\DataTransferObjects;

use Domain\Support\Payments\Interfaces\PayableInterface;

class ProviderData
{
    public function __construct(
        public readonly TransactionData $transactionData,
        public readonly PayableInterface $model,
        public readonly int $payment_method_id,
    ) {
    }
}
