<?php

declare(strict_types=1);

namespace Domain\Tenant\DataTransferObjects;

class DomainData
{
    public function __construct(
        public readonly string $domain,
        public readonly ?int $id = null,
    ) {
    }
}
