<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\Customer\TierBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Tier\Models\Tier;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class TierPolicy
{
    use ChecksWildcardPermissions;

    public function before(?User $user, string $ability, mixed $tier = null): Response|false|null
    {
        if (! tenancy()->tenant?->features()->active(TierBase::class)) {
            return Response::denyAsNotFound();
        }

        if ($tier instanceof Tier && $tier->name === config('domain.tier.default')) {
            return false;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Tier $tier): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Tier $tier): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Tier $tier): bool
    {
        if (
            $tier
                ->loadCount('customers')
                ->customers_count > 0
        ) {
            return false;
        }

        return $this->checkWildcardPermissions($user);
    }

    public function restore(User $user, Tier $tier): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function forceDelete(User $user, Tier $tier): bool
    {
        if (
            $tier
                ->loadCount('customers')
                ->customers_count > 0
        ) {
            return false;
        }

        return $this->checkWildcardPermissions($user);
    }
}
