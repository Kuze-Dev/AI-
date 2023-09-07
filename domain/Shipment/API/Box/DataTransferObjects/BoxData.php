<?php

declare(strict_types=1);

namespace Domain\Shipment\API\Box\DataTransferObjects;

class BoxData
{
    /** @param \Domain\Shipment\API\Box\DataTransferObjects\BoxItem[] $boxitems*/
    public function __construct(
        public readonly array $boxitems = [],
    ) {
    }

    public function getTotalVolume(): int|float
    {

        return array_reduce($this->boxitems, function ($carry, $boxItem) {
            return $carry + $boxItem->volume;
        }, 0);
    }

    public function getTotalWeight(): int|float
    {

        return array_reduce($this->boxitems, function ($carry, $boxItem) {
            return $carry + $boxItem->weight;
        }, 0);
    }

    public static function fromArray(array $data): self
    {
        return new self(
            boxitems: array_map(fn (array $boxitemData) => BoxItem::fromArray($boxitemData), $data),
        );
    }
}
