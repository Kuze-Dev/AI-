<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\Service\ServiceBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class ServiceOrderPolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (TenantFeatureSupport::inactive(ServiceBase::class)) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, ServiceOrder $serviceOrder): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
