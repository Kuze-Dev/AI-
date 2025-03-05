<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

use Illuminate\Http\UploadedFile;

readonly class BlockData
{
    public function __construct(
        public string $name,
        public string $component,
        public string $blueprint_id,
        public bool $is_fixed_content,
        public UploadedFile|string|array|null $image = null,
        public ?array $data = null,
        public array $sites = []
    ) {
    }
}
