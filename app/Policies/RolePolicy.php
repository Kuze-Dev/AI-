<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    use HandlesAuthorization;
    use ChecksWildcardPermissions;

    public function before(?User $user, string $ability, mixed $role = null): bool|null
    {
        if ($role instanceof Role && $role->name === config('domain.role.super_admin')) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Role $role): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Role $role): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
