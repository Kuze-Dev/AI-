<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

readonly class PlaceOrderAddressData
{
    public function __construct(
        public int $shipping,
        public int $billing
    ) {
    }
}
