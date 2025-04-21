<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

readonly class GuestPlaceOrderAddressData
{
    public function __construct(
        public GuestOrderAddressData $shipping,
        public GuestOrderAddressData $billing,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(...$data);
    }
}
