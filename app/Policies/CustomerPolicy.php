<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\Customer\CustomerBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Customer\Models\Customer;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class CustomerPolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (! tenancy()->tenant?->features()->active(CustomerBase::class)) {
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

    public function delete(User $user, ?Customer $customer = null): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function restore(User $user, ?Customer $customer = null): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function forceDelete(User $user, ?Customer $customer = null): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function sendRegisterInvitation(User $user, Customer $customer): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
