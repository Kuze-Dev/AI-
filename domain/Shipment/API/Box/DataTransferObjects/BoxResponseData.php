<?php

declare(strict_types=1);

namespace Domain\Shipment\API\Box\DataTransferObjects;

class BoxResponseData
{
    public function __construct(
        public readonly BoxData $boxData,
        public readonly string $dimension_units,
        public readonly int|float $length,
        public readonly int|float $width,
        public readonly int|float $height,
        public readonly int|float $weight,
        public readonly int|float $volume,
    ) {}
}
