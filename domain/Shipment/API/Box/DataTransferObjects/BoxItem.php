<?php

declare(strict_types=1);

namespace Domain\Shipment\API\Box\DataTransferObjects;

class BoxItem
{
    public function __construct(
        public readonly string $product_id,
        public readonly int|float $length,
        public readonly int|float $width,
        public readonly int|float $height,
        public readonly int|float $weight,
        public readonly int|float $volume,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            product_id: $data['product_id'],
            length: $data['length'],
            width: $data['width'],
            height: $data['height'],
            weight: $data['weight'],
            volume: ($data['length'] * $data['width'] * $data['height']),
        );
    }
}
