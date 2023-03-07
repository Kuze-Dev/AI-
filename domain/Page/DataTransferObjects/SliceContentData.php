<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

class SliceContentData
{
    public function __construct(
        public readonly int $slice_id,
        public readonly ?array $data = null,
        public readonly ?int $id = null,
    ) {
    }
}
