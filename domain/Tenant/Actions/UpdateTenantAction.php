<?php

declare(strict_types=1);

namespace Domain\Tenant\Actions;

use Domain\Tenant\DataTransferObjects\TenantData;
use Domain\Tenant\Models\Tenant;
use Illuminate\Support\Arr;

class UpdateTenantAction
{
    public function __construct(
        protected CreateDomainAction $createDomain,
        protected UpdateDomainAction $updateDomain,
        protected DeleteDomainAction $deleteDomain,
    ) {
    }

    public function execute(Tenant $tenant, TenantData $tenantData): Tenant
    {
        $tenant->update(['name' => $tenantData->name]);

        foreach ($tenant->domains->whereNotIn('id', Arr::pluck($tenantData->domains, 'id')) as $domain) {
            $this->deleteDomain->execute($domain);
        }

        foreach ($tenantData->domains as $domainData) {
            if ($domain = $tenant->domains->firstWhere('id', $domainData->id)) {
                $this->updateDomain->execute($domain, $domainData);

                continue;
            }

            $this->createDomain->execute($tenant, $domainData);
        }

        return $tenant;
    }
}
