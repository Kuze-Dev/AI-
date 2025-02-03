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
        if (auth()->user()?->can('canSuspendTenant')) {

            $tenant->update([
                'name' => $tenantData->name,
                'is_suspended' => $tenantData->is_suspended,
            ]);

        } else {

            $tenant->update([
                'name' => $tenantData->name,
            ]);
        }

        $tenant->setInternal('mail_from_address', $tenantData->mail?->from_address);
        $tenant->setInternal('google_map_api_key', $tenantData->google_map_api_key);
        $tenant->save();

        $this->syncDomains($tenant, $tenantData->domains);

        if (is_array($tenantData->features)) {
            $this->syncFeatures($tenant, $tenantData->getNormalizedFeatureNames());
        }

        return $tenant;
    }

    protected function syncDomains(Tenant $tenant, array $domains): void
    {
        foreach ($tenant->domains->whereNotIn('id', Arr::pluck($domains, 'id')) as $domain) {
            $this->deleteDomain->execute($domain);
        }

        foreach ($domains as $domainData) {
            if ($domain = $tenant->domains->firstWhere('id', $domainData->id)) {
                $this->updateDomain->execute($domain, $domainData);

                continue;
            }

            $this->createDomain->execute($tenant, $domainData);
        }
    }

    protected function syncFeatures(Tenant $tenant, array $features): void
    {
        $activeFeatures = array_keys(array_filter($tenant->features()->all()));
        $inactiveFeatures = array_diff($activeFeatures, $features);

        foreach ($inactiveFeatures as $inactiveFeature) {
            $tenant->features()->deactivate($inactiveFeature);
        }

        foreach ($features as $feature) {
            $tenant->features()->activate($feature);
        }
    }
}
