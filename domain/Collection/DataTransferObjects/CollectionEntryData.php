<?php

declare(strict_types=1);

namespace Domain\Collection\DataTransferObjects;

class CollectionEntryData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $slug,
        public readonly array $data,
    ) {
    }
}
