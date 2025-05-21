<?php

declare(strict_types=1);

namespace Support\MetaData\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class MetaDataData
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $author = null,
        public readonly ?string $description = null,
        public readonly ?string $keywords = null,
        public readonly UploadedFile|string|null $image = null,
        public readonly ?string $image_alt_text = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'] ?? null,
            author: $data['author'] ?? null,
            description: $data['description'] ?? null,
            keywords: $data['keywords'] ?? null,
            image: $data['image'] ?? null,
            image_alt_text: $data['image_alt_text'] ?? null,
        );
    }
}
