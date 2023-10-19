<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Tenant\Models\Tenant;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;

class TenantPolicy
{
    use HandlesAuthorization;
    use ChecksWildcardPermissions;

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Tenant $tenant): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Tenant $tenant): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Tenant $tenant): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function updateFeatures(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function canSuspendTenant(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
