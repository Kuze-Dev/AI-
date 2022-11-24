<?php

declare(strict_types=1);

namespace Domain\Page\DataTransferObjects;

class PageContentData
{
    public function __construct(
        public readonly array $data
    ) {
    }
}
