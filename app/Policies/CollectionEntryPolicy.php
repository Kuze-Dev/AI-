<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Collection\Models\CollectionEntry;
use Illuminate\Foundation\Auth\User;

class CollectionEntryPolicy
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
     * @param CollectionEntry $collectionEntry
     *
     * @return bool
     */
    public function view(User $user, CollectionEntry $collectionEntry): bool
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
     * @param CollectionEntry $collectionEntry
     *
     * @return bool
     */
    public function update(User $user, CollectionEntry $collectionEntry): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    /**
     * @param User $user
     * @param CollectionEntry $collectionEntry
     *
     * @return bool
     */
    public function delete(User $user, CollectionEntry $collectionEntry): bool
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
