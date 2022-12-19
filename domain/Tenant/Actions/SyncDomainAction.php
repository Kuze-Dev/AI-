<?php

declare(strict_types=1);

namespace Domain\Tenant\Actions;

use Domain\Tenant\DataTransferObjects\DomainData;
use Domain\Tenant\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class SyncDomainAction
{
    public function execute(Tenant $tenant, DomainData $domainData): Domain
    {
        return $tenant->domains()->updateOrCreate(
            ['id' => $domainData->id],
            ['domain' => $domainData->domain]
        );
    }
}
