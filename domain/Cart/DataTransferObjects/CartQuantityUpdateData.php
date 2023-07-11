<?php

declare(strict_types=1);

namespace Domain\Cart\DataTransferObjects;

class CartQuantityUpdateData
{
    public function __construct(
        public readonly int $cart_line_id,
        public readonly string $action,
        public readonly ?int $quantity,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            cart_line_id: $data['cartLineId'],
            action: $data['action'],
            quantity: $data['quantity'] ?? null,
        );
    }
}
