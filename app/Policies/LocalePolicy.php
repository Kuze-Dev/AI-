<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;
use App\Features\CMS\Internationalization;
use Domain\Internationalization\Models\Locale;
use App\Policies\Concerns\ChecksWildcardPermissions;

class LocalePolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (tenancy()->tenant?->features()->inactive(Internationalization::class)) {
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
