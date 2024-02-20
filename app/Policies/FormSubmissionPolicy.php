<?php

declare(strict_types=1);

namespace App\Policies;

use App\Features\CMS\CMSBase;
use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Form\Models\FormSubmission;
use Domain\Tenant\TenantFeatureSupport;
use Illuminate\Auth\Access\Response;
use Illuminate\Foundation\Auth\User;

class FormSubmissionPolicy
{
    use ChecksWildcardPermissions;

    public function before(): ?Response
    {
        if (TenantFeatureSupport::inactive(CMSBase::class)) {
            return Response::denyAsNotFound();
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, FormSubmission $formSubmission): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
