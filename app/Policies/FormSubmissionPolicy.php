<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Form\Models\FormSubmission;
use Illuminate\Foundation\Auth\User;

class FormSubmissionPolicy
{
    use ChecksWildcardPermissions;

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, FormSubmission $formSubmission): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
