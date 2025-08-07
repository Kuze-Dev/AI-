<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Admin\Models\Admin;
use Domain\Tenant\Models\TenantApiKey;
use Illuminate\Auth\Access\Response;

class TenantApiKeyPolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (! config('custom.strict_api')) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(Admin $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(Admin $user, TenantApiKey $tenantApiKey): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(Admin $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(Admin $user, TenantApiKey $tenantApiKey): bool
    {
        if ($user->hasRole(config()->string('domain.role.super_admin'))) {
            return true;
        }

        if ($user->id !== $tenantApiKey->admin_id) {
            return false;
        }

        return $this->checkWildcardPermissions($user);
    }

    public function delete(Admin $user, TenantApiKey $tenantApiKey): bool
    {

        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(Admin $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
