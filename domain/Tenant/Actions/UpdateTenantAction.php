<?php

namespace Domain\Tenant\Actions;

use Domain\Tenant\DataTransferObjects\TenantData;
use Domain\Tenant\Models\Tenant;

class UpdateTenantAction
{
    public function execute(Tenant $tenant, TenantData $tenantData): Tenant
    {
        $tenant->update(['name' => $tenantData->name]);

        $oldDomains = $tenant->domains->pluck('domain');

        $detached = $oldDomains->diff($tenantData->domains);
        $attached = collect($tenantData->domains)->diff($oldDomains);

        $tenant->domains->whereIn('domain', $detached)->each->delete();

        foreach ($attached as $domain) {
            $tenant->createDomain(['domain' => $domain]);
        }

        return $tenant;
    }
}
