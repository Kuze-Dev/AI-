<?php

declare(strict_types=1);

namespace Domain\Payments\DataTransferObjects;

class PayPalPaymentData
{
    public function __construct(
        public readonly string $redirect_urls,
        public readonly TransactionData $transactions,
        public readonly string $intent = 'sale',
    ) {}
}
