<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\ECommerce\ECommerceBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Discount\Models\Discount;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class DiscountPolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (TenantFeatureSupport::inactive(ECommerceBase::class)) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Discount $discount): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Discount $discount): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Discount $discount): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function restore(User $user, Discount $discount): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function forceDelete(User $user, Discount $discount): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
