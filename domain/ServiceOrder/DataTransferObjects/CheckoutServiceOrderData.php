<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

class CheckoutServiceOrderData
{
    public function __construct(
        public readonly string $payment_method,
        public readonly string $reference_id
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            payment_method: $data['payment_method'],
            reference_id: $data['reference_id']
        );
    }
}
