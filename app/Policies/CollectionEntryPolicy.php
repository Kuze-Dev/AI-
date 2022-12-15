<?php 

declare (strict_types = 1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Collection\Models\CollectionEntry;
use Illuminate\Foundation\Auth\User;

class CollectionEntryPolicy 
{
    use ChecksWildcardPermissions;

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, CollectionEntry $collectionEntry): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function configure(User $user, CollectionEntry $collectionEntry): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, CollectionEntry $collectionEntry): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, CollectionEntry $collectionEntry): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}