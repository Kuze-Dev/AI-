<?php

namespace Domain\Support\Payments\DataTransferObjects;


class PayPalPaymentData
{
    public function __construct(
        public readonly string $intent = 'sale',
        public readonly string $redirect_urls,
        public readonly TransactionData $transactions,
       
    ) {
    }

  
}
