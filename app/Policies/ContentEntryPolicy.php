<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\CMS\CMSBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Content\Models\ContentEntry;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class ContentEntryPolicy
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

    public function view(User $user, ContentEntry $contentEntry): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, ContentEntry $contentEntry): bool
    {

        /** @var \Domain\Admin\Models\Admin */
        $admin = $user;

        if ($admin->hasRole(config()->string('domain.role.super_admin'))) {

            return true;
        }

        if ($admin->can('site.siteManager')) {

            $contentEntrySites = $contentEntry->sites->pluck('id')->toArray();
            $userSites = $admin->userSite->pluck('id')->toArray();

            $intersection = array_intersect($contentEntrySites, $userSites);

            return (count($intersection) > 0) && $this->checkWildcardPermissions($user);
        }

        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, ContentEntry $contentEntry): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
