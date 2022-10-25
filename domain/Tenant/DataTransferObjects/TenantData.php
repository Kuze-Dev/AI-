<?php

namespace Domain\Tenant\DataTransferObjects;

class TenantData
{
    /** @param array<string> $domains */
    public function __construct(
        public readonly string $name,
        public readonly ?array $domains = [],
    ) {
    }
}
