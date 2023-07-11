<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\ECommerce\ECommerceBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Taxation\Models\TaxZone;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class TaxZonePolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if ( ! tenancy()->tenant?->features()->active(ECommerceBase::class)) {
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
