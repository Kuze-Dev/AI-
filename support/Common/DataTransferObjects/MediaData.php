<?php

declare(strict_types=1);

namespace Support\Common\DataTransferObjects;

use Illuminate\Http\UploadedFile;

readonly class MediaData
{
    public function __construct(
        public UploadedFile|string $media,
        public array $custom_properties = [],
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
