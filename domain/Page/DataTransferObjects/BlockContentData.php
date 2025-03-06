<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

use Domain\Internationalization\DataTransferObjects\TranslationDTO;

class BlockContentData extends TranslationDTO
{
    public function __construct(
        public readonly int $block_id,
        public readonly ?array $data = [],
        public readonly ?int $id = null,
    ) {}
}
