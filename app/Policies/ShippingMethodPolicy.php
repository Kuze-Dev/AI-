<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\ECommerce\ShippingStorePickup;
use App\Features\ECommerce\ShippingUps;
use App\Features\ECommerce\ShippingUsps;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class ShippingMethodPolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if ( ! tenancy()->tenant?->features()->someAreActive([
            ShippingUsps::class,
            ShippingUps::class,
            ShippingStorePickup::class,
        ])) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, ShippingMethod $shippingMethod): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, ShippingMethod $shippingMethod): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, ShippingMethod $shippingMethod): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
