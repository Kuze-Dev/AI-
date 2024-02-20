<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\CMS\CMSBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Page\Models\Page;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;
use Illuminate\Support\Facades\Auth;

class PagePolicy
{
    use ChecksWildcardPermissions;

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

    public function view(User $user, Page $page): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Page $page): bool
    {
        if (Auth::user()?->hasRole(config('domain.role.super_admin'))) {

            return true;
        }

        if ($user->can('site.siteManager')) {

            /** @var \Domain\Admin\Models\Admin */
            $admin = $user;

            $pageSites = $page->sites->pluck('id')->toArray();
            $userSites = $admin->userSite->pluck('id')->toArray();

            $intersection = array_intersect($pageSites, $userSites);

            return (count($intersection) === count($pageSites)) && $this->checkWildcardPermissions($user);
        }

        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Page $page): bool
    {
        if ($page->isHomePage()) {
            return false;
        }

        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
