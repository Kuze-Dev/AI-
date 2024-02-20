<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\Shopconfiguration\Shipping\ShippingStorePickup;
use App\Features\Shopconfiguration\Shipping\ShippingUps;
use App\Features\Shopconfiguration\Shipping\ShippingUsps;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class ShippingMethodPolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (! TenantFeatureSupport::someAreActive([
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
