<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\CMS\CMSBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class TaxonomyPolicy
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

    public function view(User $user, Taxonomy $taxonomy): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Taxonomy $taxonomy): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Taxonomy $taxonomy): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
