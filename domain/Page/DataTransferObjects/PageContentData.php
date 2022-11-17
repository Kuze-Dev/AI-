<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

use Carbon\Carbon;

class PageContentData
{
    public function __construct(
        public readonly string $name,
        public readonly array $data,
        public readonly ?Carbon $published_at = null,
    ) {
    }
}
