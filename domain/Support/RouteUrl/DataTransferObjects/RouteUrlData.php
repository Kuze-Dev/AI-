<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\DataTransferObjects;

class RouteUrlData
{
    public function __construct(
        public ?string $url,
    ) {
    }
}
