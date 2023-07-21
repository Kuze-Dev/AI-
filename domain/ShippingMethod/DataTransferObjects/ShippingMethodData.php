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
        public readonly bool $status,
        public readonly ?string $description = null,
        public readonly UploadedFile|string|null $logo = null,
        public readonly array $ship_from_address = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            subtitle: $data['subtitle'],
            driver: $data['driver'],
            status: $data['status'] ?? null,
            description: $data['description'] ?? null,
            logo: $data['logo'] ?? null,
            ship_from_address: $data['ship_from_address'] ?? [],
        );
    }
}
