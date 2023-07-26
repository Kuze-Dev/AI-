<?php

declare(strict_types=1);

namespace Domain\ShippingMethod\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class ShippingMethodData
{
    public function __construct(
        public readonly string $title,
        public readonly string $subtitle,
        public readonly string $driver,
        public readonly string $shipper_address,
        public readonly string $shipper_city,
        public readonly string $shipper_zipcode,
        public readonly int $shipper_country_id,
        public readonly int $shipper_state_id,
        public readonly bool $active,
        public readonly ?string $description = null,
        public readonly UploadedFile|string|null $logo = null,
        // public readonly array $ship_from_address = [],
    ) {
    }

    public static function fromArray(array $data): self
    {

        return new self(
            title: $data['title'],
            subtitle: $data['subtitle'],
            driver: $data['driver'],
            shipper_country_id :(int) $data['shipper_country_id'],
            shipper_state_id : (int) $data['shipper_state_id'],
            shipper_address : $data['shipper_address'],
            shipper_city : $data['shipper_city'],
            shipper_zipcode : $data['shipper_zipcode'],
            active: $data['status'] ?? null,
            description: $data['description'] ?? null,
            logo: $data['logo'] ?? null,
            // ship_from_address: $data['ship_from_address'] ?? [],
        );
    }
}
