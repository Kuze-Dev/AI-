<?php

declare(strict_types=1);

namespace Domain\Support\Common\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class MediaData
{
    public function __construct(
        public readonly UploadedFile|string $media,
        public readonly array $custom_properties = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            media: $data['media'],
            custom_properties: $data['custom_properties'] ?? [],
        );
    }
}
