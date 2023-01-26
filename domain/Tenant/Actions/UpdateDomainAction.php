<?php

declare(strict_types=1);

namespace Domain\Tenant\Actions;

use Domain\Tenant\DataTransferObjects\DomainData;
use Stancl\Tenancy\Database\Models\Domain;

class UpdateDomainAction
{
    public function execute(Domain $domain, DomainData $domainData): Domain
    {
        $domain->loadMissing('tenant');
        $domain->update(['domain' => $domainData->domain]);

        return $domain;
    }
}
