<?php

declare(strict_types=1);

namespace Domain\Admin\DataTransferObjects;

readonly class AdminData
{
    /**
     * @param  array<int>  $roles
     * @param  array<int>  $permissions
     */
    public function __construct(
        public string $first_name,
        public string $last_name,
        public ?string $email = null,
        public ?string $password = null,
        public ?string $timezone = null,
        public ?bool $active = null,
        public ?array $roles = null,
        public ?array $permissions = null,
    ) {}
}
