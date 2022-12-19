<?php

declare(strict_types=1);

namespace Domain\Collection\DataTransferObjects;

use Carbon\Carbon;

class CollectionEntryData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $slug,
        public readonly array $data,
        // public readonly ?Carbon $published_at = null,
    ) {
    }
}
