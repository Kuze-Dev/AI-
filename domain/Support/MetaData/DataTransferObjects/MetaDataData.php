<?php

declare(strict_types=1);

namespace Domain\Support\MetaData\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class MetaDataData
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $author = null,
        public readonly ?string $description = null,
        public readonly ?string $keywords = null,
        public readonly UploadedFile|string|null $image = null,
    ) {
    }
}
