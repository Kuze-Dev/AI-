<?php

declare(strict_types=1);

namespace Domain\Site\DataTransferObjects;

class SiteData
{
    public function __construct(
        public readonly string $name,
        public readonly ?string $deploy_hook = null,
        public readonly ?array $site_manager = null,
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            deploy_hook: $data['deploy_hook'] ?? null,
            site_manager: $data['site_manager'] ?? null,
        );
    }
}
