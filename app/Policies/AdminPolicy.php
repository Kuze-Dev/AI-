<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Admin\Models\Admin;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;

class AdminPolicy
{
    use ChecksWildcardPermissions;
    use HandlesAuthorization;

    public function before(?User $user, string $ability, mixed $admin = null): ?bool
    {
        if ($admin instanceof Admin && $admin->isZeroDayAdmin()) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Admin $admin): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Admin $admin): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Admin $admin): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function restore(User $user, Admin $admin): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function restoreAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function forceDelete(User $user, Admin $admin): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function forceDeleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function resendVerification(User $user, Admin $admin): bool
    {
        if ($admin->hasVerifiedEmail() || ! is_null($admin->deleted_at)) {
            return false;
        }

        return $this->checkWildcardPermissions($user);
    }

    public function sendPasswordReset(User $user, Admin $admin): bool
    {
        if (! is_null($admin->deleted_at)) {
            return false;
        }

        return $this->checkWildcardPermissions($user);
    }

    public function impersonate(User $user, Admin $admin): bool
    {
        if (! is_null($admin->deleted_at)) {
            return false;
        }

        return $this->checkWildcardPermissions($user);
    }

    public function customerPrintReceipt(User $user, Admin $admin): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
