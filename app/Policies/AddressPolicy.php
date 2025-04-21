<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\Customer\AddressBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class AddressPolicy
{
    use ChecksWildcardPermissions;

    public function before(?User $user, string $ability, mixed $address = null): ?Response
    {
        if (TenantFeatureSupport::inactive(AddressBase::class)) {
            return Response::denyAsNotFound();
        }

        if (
            $user instanceof Customer &&
            $address instanceof Address
        ) {
            return $address->customer?->is($user)
                ? Response::allow()
                : Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Address $address): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Address $address): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Address $address): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
