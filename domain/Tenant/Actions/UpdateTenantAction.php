<?php

declare(strict_types=1);

namespace Domain\Tenant\Actions;

use Domain\Tenant\DataTransferObjects\TenantData;
use Domain\Tenant\Models\Tenant;

class UpdateTenantAction
{
    public function execute(Tenant $tenant, TenantData $tenantData): Tenant
    {
        $tenant->update(['name' => $tenantData->name]);

        if ( ! empty($tenantData->domains)) {
            $this->syncDomains($tenant, $tenantData);
        }

        return $tenant;
    }

    protected function syncDomains(Tenant $tenant, TenantData $tenantData): void
    {
        $currentDomains = $tenant->domains->pluck('domain');

        $detached = $currentDomains->diff($tenantData->domains);
        $attached = collect($tenantData->domains)->diff($currentDomains);

        $tenant->domains->whereIn('domain', $detached)->each->delete();

        foreach ($attached as $domain) {
            $tenant->createDomain(['domain' => $domain]);
        }
    }
}
