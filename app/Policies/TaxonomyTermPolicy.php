<?php

declare(strict_types=1);

namespace App\Policies;

use App\Policies\Concerns\ChecksWildcardPermissions;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Foundation\Auth\User;

class TaxonomyTermPolicy
{
    use ChecksWildcardPermissions;

    public function viewAny(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function view(User $user, TaxonomyTerm $taxonomyTerm): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function create(User $user): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function update(User $user, TaxonomyTerm $taxonomyTerm): bool
    {
        return $this->checkWildcardPermissions($user);
    }

    public function delete(User $user, TaxonomyTerm $taxonomyTerm): bool
    {
        return $this->checkWildcardPermissions($user);
    }
}
