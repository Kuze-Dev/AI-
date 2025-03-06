<?php

declare(strict_types=1);

namespace Domain\Role\DataTransferObjects;

readonly class RoleData
{
    /** @param  array<int, int>  $permissions */
    public function __construct(
        public string $name,
        public ?string $guard_name = null,
        public array $permissions = [],
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            name: $data['name'],
            guard_name: $data['guard_name'] ?? null,
            permissions: array_map(fn (string|int $key) => (int) $key, $data['permissions'] ?? []),
        );
    }
}
