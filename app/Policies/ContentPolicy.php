<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Content\Models\Content;
use Illuminate\Foundation\Auth\User;

class ContentPolicy
{
    use ChecksWildcardPermissions;

    /**
     * @param User $user
     *
     * @return bool
     */
    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    /**
     * @param User $user
     * @param Content $content
     *
     * @return bool
     */
    public function view(User $user, Content $content): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    /**
     * @param User $user
     * @param Content $content
     *
     * @return bool
     */
    public function update(User $user, Content $content): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    /**
     * @param User $user
     * @param Content $content
     *
     * @return bool
     */
    public function delete(User $user, Content $content): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
