<?php

declare(strict_types=1);

namespace Domain\Role\DataTransferObjects;

class RoleData
{
    /** @param  array<int>  $permissions */
    public function __construct(
        public readonly string $name,
        public readonly ?string $guard_name = null,
        public readonly array $permissions = [],
    ) {
    }
}
