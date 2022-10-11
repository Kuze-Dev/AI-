<?php

declare(strict_types=1);

namespace Domain\Admin\DataTransferObjects;

class AdminData
{
    /**
     * @param  array<int>  $roles
     * @param  array<int>  $permissions
     */
    public function __construct(
        public readonly string $first_name,
        public readonly string $last_name,
        public readonly string $email,
        public readonly ?string $password = null,
        public readonly bool $active = true,
        public readonly ?string $timezone = null,
        public readonly array $roles = [],
        public readonly array $permissions = [],
    ) {
    }
}
