<?php

declare(strict_types=1);

namespace Domain\Shipment\API\Box\DataTransferObjects;

use Domain\Shipment\Enums\UnitEnum;

class BoxData
{
    /** @param  \Domain\Shipment\API\Box\DataTransferObjects\BoxItem[]  $boxitems*/
    public function __construct(
        public readonly array $boxitems = [],
    ) {}

    public function getTotalVolume(): int|float
    {

        return array_reduce($this->boxitems, fn ($carry, $boxItem) => $carry + $boxItem->volume, 0);
    }

    public function getTotalWeight(?string $unit = ''): int|float
    {

        $totalWeight = array_reduce($this->boxitems, fn ($carry, $boxItem) => $carry + $boxItem->weight, 0);

        return match ($unit) {
            UnitEnum::KG->value => $totalWeight * 0.45359237,
            default => $totalWeight,
        };
    }

    public static function fromArray(array $data): self
    {
        return new self(
            boxitems: array_map(fn (array $boxitemData) => BoxItem::fromArray($boxitemData), $data),
        );
    }
}
