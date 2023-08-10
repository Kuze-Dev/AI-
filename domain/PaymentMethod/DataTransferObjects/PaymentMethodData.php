<?php

declare(strict_types=1);

namespace Domain\PaymentMethod\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class PaymentMethodData
{
    public function __construct(
        public readonly string $title,
        public readonly string $gateway,
        public readonly string $subtitle,
        public readonly bool $status,
        public readonly UploadedFile|string|null $logo = null,
        public readonly ?string $description,
        public readonly ?string $instruction,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            gateway: $data['gateway'],
            subtitle: $data['subtitle'],
            logo: $data['logo'] ?? null,
            description: $data['description'] ?? null,
            instruction: $data['instruction'] ?? null,
            status: $data['status'],
        );
    }
}
