<?php

namespace Domain\Admin\DataTransferObjects;

class RoleData
{
    /**
     * @param  array<int>  $permissions
     */
    public function __construct(
        public readonly string $name,
        public readonly ?string $guard_name = null,
        public readonly array $permissions = [],
    ) {
    }
}
