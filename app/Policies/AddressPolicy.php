<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\ECommerce\ECommerceBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Illuminate\Foundation\Auth\User;
use Domain\Customer\Models\Address;
use Illuminate\Auth\Access\Response;

class AddressPolicy
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
