<?php

declare(strict_types=1);

namespace Domain\Site\DataTransferObjects;

readonly class SiteData
{
    public function __construct(
        public string $name,
        public ?string $domain = null,
        public ?string $deploy_hook = null,
        public ?array $site_manager = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            domain: $data['domain'] ?? null,
            deploy_hook: $data['deploy_hook'] ?? null,
            site_manager: $data['site_manager'] ?? null,
        );
    }
}
