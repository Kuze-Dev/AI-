<?php

declare(strict_types=1);

namespace Domain\Site\DataTransferObjects;

class SiteData
{
    public function __construct(
        public readonly string $name,
    ) {
    }
}
