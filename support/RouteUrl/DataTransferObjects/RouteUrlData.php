<?php

declare(strict_types=1);

namespace Support\RouteUrl\DataTransferObjects;

class RouteUrlData
{
    public function __construct(
        public ?string $url,
        public bool $is_override,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            url: $data['url'] ?? null,
            is_override: $data['is_override'] ?? false
        );
    }
}
