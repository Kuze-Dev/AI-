<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Page\Models\Page;
use Illuminate\Foundation\Auth\User;

class PagePolicy
{
    use ChecksWildcardPermissions;

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Page $page): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function configure(User $user, Page $page): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Page $page): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Page $page): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
