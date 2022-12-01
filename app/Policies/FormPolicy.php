<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Form\Models\Form;
use Illuminate\Foundation\Auth\User;

class FormPolicy
{
    use ChecksWildcardPermissions;

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, Form $form): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, Form $form): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, Form $form): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function deleteAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
