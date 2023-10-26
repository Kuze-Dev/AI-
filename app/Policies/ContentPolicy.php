<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\CMS\CMSBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Content\Models\Content;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class ContentPolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (! tenancy()->tenant?->features()->active(CMSBase::class)) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Content $content): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Content $content): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Content $content): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
