<?php

declare(strict_types=1);

namespace Domain\Tenant\Actions;

use Domain\Tenant\DataTransferObjects\DomainData;
use Domain\Tenant\Models\Tenant;
use Stancl\Tenancy\Database\Models\Domain;

class CreateDomainAction
{
    public function execute(Tenant $tenant, DomainData $domainData): Domain
    {
        return $tenant->domains()->create(['domain' => $domainData->domain]);
    }
}
