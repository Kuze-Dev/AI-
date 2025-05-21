<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\Shopconfiguration\TaxZone as ShopconfigurationTaxZone;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Taxation\Models\TaxZone;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class TaxZonePolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (TenantFeatureSupport::inactive(ShopconfigurationTaxZone::class)) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, TaxZone $taxZone): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, TaxZone $taxZone): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, TaxZone $taxZone): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
