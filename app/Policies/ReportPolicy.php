<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\ECommerce\ECommerceBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class ReportPolicy
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

}
