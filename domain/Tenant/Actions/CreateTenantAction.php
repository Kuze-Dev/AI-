<?php

declare(strict_types=1);

namespace Domain\Tenant\Actions;

use Domain\Tenant\DataTransferObjects\TenantData;
use Domain\Tenant\Models\Tenant;
use InvalidArgumentException;

class CreateTenantAction
{
    public function __construct(
        protected CreateDomainAction $createDomain,
    ) {
    }

    public function execute(TenantData $tenantData): Tenant
    {
        if ($tenantData->database === null) {
            throw new InvalidArgumentException('$tenantData->database is required when creating a tenant');
        }

        /** @var Tenant $tenant */
        $tenant = new Tenant(['name' => $tenantData->name]);

        $tenant->setInternal('db_host', $tenantData->database->host);
        $tenant->setInternal('db_port', $tenantData->database->port);
        $tenant->setInternal('db_name', $tenantData->database->name);
        $tenant->setInternal('db_username', $tenantData->database->username);
        $tenant->setInternal('db_password', $tenantData->database->password);
        $tenant->setInternal('bucket', $tenantData->bucket?->bucket);
        $tenant->setInternal('bucket_driver', $tenantData->bucket?->driver);
        $tenant->setInternal('bucket_access_key', $tenantData->bucket?->access_key);
        $tenant->setInternal('bucket_secret_key', $tenantData->bucket?->secret_key);
        $tenant->setInternal('bucket_endpoint', $tenantData->bucket?->endpoint);
        $tenant->setInternal('bucket_url', $tenantData->bucket?->url);
        $tenant->setInternal('bucket_region', $tenantData->bucket?->region);
        $tenant->setInternal('bucket_style_endpoint', $tenantData->bucket?->style_endpoint);
        $tenant->setInternal('mail_from_address', $tenantData->mail?->from_address);
        $tenant->setInternal('cors_allowed_origins', $tenantData->cors_allowed_origins);
        $tenant->setInternal('ip_white_list',$tenantData->ip_white_list);
        $tenant->setInternal('cors_allowed_origins', $tenantData->cors_allowed_origins ?? ['*']);
        $tenant->setInternal('ip_white_list', $tenantData->ip_white_list);

        $tenant->save();

        foreach ($tenantData->domains as $domain) {
            $this->createDomain->execute($tenant, $domain);
        }

        if (is_array($tenantData->features)) {

            foreach ($tenantData->features as $feature) {
                $tenant->features()->activate($feature);
            }
        }

        return $tenant;
    }
}
