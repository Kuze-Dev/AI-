<?php

declare(strict_types=1);

namespace Domain\Order\DataTransferObjects;

use Domain\Address\Models\State;

readonly class GuestStatesData
{
    public function __construct(
        public State $shippingState,
        public State $billingState,
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
