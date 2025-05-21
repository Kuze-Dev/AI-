<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\CMS\CMSBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Menu\Models\Menu;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class MenuPolicy
{
    use ChecksWildcardPermissions;
    use HandlesAuthorization;

    public function before(): ?Response
    {
        if (TenantFeatureSupport::inactive(CMSBase::class)) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Menu $menu): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Menu $menu): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Menu $menu): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
