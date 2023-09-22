<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\Service\ServiceBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Service\Models\Service;
use Illuminate\Foundation\Auth\User;
use Illuminate\Auth\Access\Response;

class ServicePolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if ( ! tenancy()->tenant?->features()->active(ServiceBase::class)) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Service $service): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Service $service): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Service $service): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
