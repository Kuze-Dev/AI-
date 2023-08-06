<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\ECommerce\ECommerceBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Illuminate\Foundation\Auth\User;
use Domain\Customer\Models\Customer;
use Illuminate\Auth\Access\Response;

class CustomerPolicy
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

    public function view(User $user, Customer $customer): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function restore(User $user, Customer $customer): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function forceDelete(User $user, Customer $customer): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function sendRegisterInvitation(User $user, Customer $customer): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
