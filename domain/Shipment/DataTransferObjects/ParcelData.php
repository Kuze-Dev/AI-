<?php

declare(strict_types=1);

namespace Domain\Shipment\DataTransferObjects;

class ParcelData
{
    public function __construct(
        public readonly ShipFromAddressData $ship_from_address,
        public readonly string $pounds,
        public readonly string $ounces,
        public readonly string $zip_origin,
        public readonly ?string $parcel_value = null,
        public readonly ?string $height = null,
        public readonly ?string $width = null,
        public readonly ?string $length = null,
    ) {
    }

    public function toArray(): array
    {
        return array_filter(get_object_vars($this), fn ($value) => ! is_null($value) && $value !== '');
    }
}
