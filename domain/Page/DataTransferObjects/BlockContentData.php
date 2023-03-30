<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

class BlockContentData
{
    public function __construct(
        public readonly int $block_id,
        public readonly ?array $data = null,
        public readonly ?int $id = null,
    ) {
    }
}
