<?php

declare(strict_types=1);

namespace Domain\Support\MetaTag\DataTransferObjects;

class MetaTagData
{
    public function __construct(
        public readonly ?string $title = null,
        public readonly ?string $author = null,
        public readonly ?string $description = null,
        public readonly ?string $keywords = null,
    ) {
    }
}
