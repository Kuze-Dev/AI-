<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

class ServiceOrderAdditionalChargeData
{
    public function __construct(
        public float $price,
        public int $quantity,
    ) {}

    public static function fromArray(array $data): self
    {
        $data['price'] = (float) $data['price'];
        $data['quantity'] = (int) $data['quantity'];

        return new self(...$data);
    }
}
