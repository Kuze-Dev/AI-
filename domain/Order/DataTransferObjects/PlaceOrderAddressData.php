<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class PlaceOrderAddressData
{
    public function __construct(
        public readonly int $shipping,
        public readonly int $billing
    ) {
    }
}
