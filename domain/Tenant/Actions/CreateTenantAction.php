<?php

declare(strict_types=1);

namespace Domain\Tenant\Actions;

use Domain\Tenant\DataTransferObjects\TenantData;
use Domain\Tenant\Models\Tenant;

class CreateTenantAction
{
    public function execute(TenantData $tenantData): Tenant
    {
        /** @var Tenant $tenant */
        $tenant = Tenant::create(['name' => $tenantData->name]);

        foreach ($tenantData->domains as $domain) {
            $tenant->createDomain(['domain' => $domain]);
        }

        return $tenant;
    }
}
