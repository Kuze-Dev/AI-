<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

class GuestPlaceOrderAddressData
{
    public function __construct(
        public readonly GuestOrderAddressData $shipping,
        public readonly GuestOrderAddressData $billing,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
