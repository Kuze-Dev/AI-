<?php

declare(strict_types=1);

namespace App\Filament\Resources\RoleResource\Support;

use Illuminate\Support\Collection;
use Spatie\Permission\Models\Permission;

class PermissionGroup
{
    /** @param  Collection<int, Permission>  $abilities */
    public function __construct(
        public Permission $main,
        public Collection $abilities
    ) {}

    /** @param  Collection<int, Permission>  $permissions */
    public static function make(Collection $permissions): self
    {
        /** @var Permission $main */
        $main = $permissions->firstWhere(fn (Permission $permission) => ! (explode('.', $permission->name, 2)[1] ?? null));
        $abilities = $permissions->filter(fn (Permission $permission) => (bool) (explode('.', $permission->name, 2)[1] ?? false));

        return new self($main, $abilities);
    }

    /** @return Collection<int, string> */
    public function getParts(): Collection
    {
        return $this->abilities->map(fn (Permission $permission) => explode('.', $permission->name, 2)[1]);
    }
}
