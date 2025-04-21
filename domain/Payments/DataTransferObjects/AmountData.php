<?php

declare(strict_types=1);

namespace Domain\Payments\DataTransferObjects;

class AmountData
{
    public function __construct(
        public readonly PaymentDetailsData $details,
        public readonly string $currency,
        public readonly int|float $total,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            currency: $data['currency'],
            total: $data['total'] * 100,
            details: $data['details'],
        );
    }
}
