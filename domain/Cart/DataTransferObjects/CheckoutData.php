<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

class CheckoutData
{
    public function __construct(
        public readonly array $cart_line_ids
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cart_line_ids: $data['cart_line_ids']
        );
    }
}
