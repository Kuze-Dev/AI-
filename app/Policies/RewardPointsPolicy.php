<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\ECommerce\RewardPoints;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class RewardPointsPolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (tenancy()->tenant?->features()->inactive(RewardPoints::class)) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
