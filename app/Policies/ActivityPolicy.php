<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User;
use Spatie\Activitylog\Models\Activity;

class ActivityPolicy
{
    use ChecksWildcardPermissions;
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Activity $activity): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
