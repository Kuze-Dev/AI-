<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\CMS\Internationalization;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Internationalization\Models\Locale;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class LocalePolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (TenantFeatureSupport::inactive(Internationalization::class)) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Locale $Locale): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Locale $Locale): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Locale $Locale): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
