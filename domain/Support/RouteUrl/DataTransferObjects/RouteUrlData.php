<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl\DataTransferObjects;

class RouteUrlData
{
    public function __construct(
        public string $url,
        public bool $is_override,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self($data['route_url'], $data['is_override'] ?? false);
    }
}
