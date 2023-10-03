<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\DataTransferObjects;

class ServiceOrderAdditionalChargeData
{
    public function __construct(
        public float $price,
        public int $quantity,
    ) {
    }
}
