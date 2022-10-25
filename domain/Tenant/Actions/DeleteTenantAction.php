<?php

declare(strict_types=1);

namespace Domain\Tenant\Actions;

use Domain\Tenant\Models\Tenant;

class DeleteTenantAction
{
    public function execute(Tenant $tenant): ?bool
    {
        return $tenant->delete();
    }
}
