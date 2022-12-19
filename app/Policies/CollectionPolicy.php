<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Collection\Models\Collection;
use Illuminate\Foundation\Auth\User;

class CollectionPolicy
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
     * @param Collection $collection
     *
     * @return bool
     */
    public function view(User $user, Collection $collection): bool
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
     * @param Collection $collection
     *
     * @return bool
     */
    public function configure(User $user, Collection $collection): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    /**
     * @param User $user
     * @param Collection $collection
     *
     * @return bool
     */
    public function update(User $user, Collection $collection): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    /**
     * @param User $user
     * @param Collection $collection
     *
     * @return bool
     */
    public function delete(User $user, Collection $collection): bool
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
