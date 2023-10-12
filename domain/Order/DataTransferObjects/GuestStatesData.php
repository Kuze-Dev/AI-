<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Address\Models\State;

class GuestStatesData
{
    public function __construct(
        public readonly State $shippingState,
        public readonly State $billingState,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            shippingState: $data['shippingState'],
            billingState: $data['billingState'],
        );
    }
}
