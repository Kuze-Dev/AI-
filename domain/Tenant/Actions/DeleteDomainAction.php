<?php

declare(strict_types=1);

namespace Domain\Tenant\Actions;

use Stancl\Tenancy\Database\Models\Domain;

class DeleteDomainAction
{
    public function execute(Domain $domain): ?bool
    {
        return $domain->delete();
    }
}
