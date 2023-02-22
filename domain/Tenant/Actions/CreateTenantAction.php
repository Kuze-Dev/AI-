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

        $tenant->save();

        foreach ($tenantData->domains as $domain) {
            $this->createDomain->execute($tenant, $domain);
        }

        return $tenant;
    }
}
