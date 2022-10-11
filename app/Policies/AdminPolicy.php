<?php

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Admin\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;

class AdminPolicy
{
    use HandlesAuthorization;
    use ChecksWildcardPermissions;

    public function before(User $user, string $ability, mixed $admin): mixed
    {
        if ($admin instanceof Admin && $admin->isSuperAdmin()) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): mixed
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Admin $admin): mixed
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): mixed
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Admin $admin): mixed
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Admin $admin): mixed
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): mixed
    {
        return $this->checkWildcardPermissions($user);
    }

    public function restore(User $user, Admin $admin): mixed
    {
        return $this->checkWildcardPermissions($user);
    }

    public function restoreAny(User $user): mixed
    {
        return $this->checkWildcardPermissions($user);
    }

    public function forceDelete(User $user, Admin $admin): mixed
    {
        return $this->checkWildcardPermissions($user);
    }

    public function forceDeleteAny(User $user): mixed
    {
        return $this->checkWildcardPermissions($user);
    }

    public function resendVerification(User $user, Admin $admin): mixed
    {
        if ($admin->hasVerifiedEmail()) {
            return false;
        }

        return $this->checkWildcardPermissions($user);
    }

    public function sendPasswordReset(User $user, Admin $admin): mixed
    {
        return $this->checkWildcardPermissions($user);
    }
}
