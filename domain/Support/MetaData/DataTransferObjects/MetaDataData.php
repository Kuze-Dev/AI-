<?php

declare(strict_types=1);

namespace Domain\Support\MetaData\DataTransferObjects;

class MetaDataData
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $author = null,
        public readonly ?string $description = null,
        public readonly ?string $keywords = null,
    ) {
    }
}
