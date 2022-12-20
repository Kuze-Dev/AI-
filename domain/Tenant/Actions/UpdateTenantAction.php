<?php

declare(strict_types=1);

namespace Domain\Tenant\Actions;

use Domain\Tenant\DataTransferObjects\TenantData;
use Domain\Tenant\Models\Tenant;
use Illuminate\Support\Arr;

class UpdateTenantAction
{
    public function __construct(
        protected SyncDomainAction $syncDomain,
        protected DeleteDomainAction $deleteDomain,
    ) {
    }

    public function execute(Tenant $tenant, TenantData $tenantData): Tenant
    {
        $tenant->update(['name' => $tenantData->name]);

        foreach ($tenantData->domains as $domain) {
            $this->syncDomain->execute($tenant, $domain);
        }

        foreach ($tenant->domains()->whereNotIn('id', Arr::pluck($tenantData->domains, 'id'))->get() as $domain) {
            $this->deleteDomain->execute($domain);
        }

        return $tenant;
    }
}
