<?php

declare(strict_types=1);

namespace Domain\Tenant\DataTransferObjects;

class TenantData
{
    /** @param array<DomainData> $domains */
    public function __construct(
        public readonly string $name,
        public readonly array $domains = [],
    ) {
    }

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            domains: array_map(
                fn ($data) => new DomainData(
                    id: $data['id'] ?? null,
                    domain: $data['domain'],
                ),
                $data['domains']
            )
        );
    }
}
