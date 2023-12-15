<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

class CheckoutServiceOrderData
{
    public function __construct(
        public readonly string $payment_method,
        public readonly string $reference_id,
        public readonly ?float $amount_to_pay = null
    ) {
    }

    public static function fromRequest(array $data): self
    {
        return new self(
            payment_method: $data['payment_method'],
            reference_id: $data['reference_id'],
        );
    }

    public static function fromRequestPartial(array $data): self
    {
        return new self(
            payment_method: $data['payment_method'],
            reference_id: $data['reference_id'],
            amount_to_pay: $data['amount_to_pay']
        );
    }
}
