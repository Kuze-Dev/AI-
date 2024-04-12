<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

use Illuminate\Http\UploadedFile;

class BlockData
{
    public function __construct(
        public readonly string $name,
        public readonly string $component,
        public readonly string $blueprint_id,
        public readonly bool $is_fixed_content,
        public readonly UploadedFile|string|null $image = null,
        public readonly ?array $data = null,
    ) {
    }
}
