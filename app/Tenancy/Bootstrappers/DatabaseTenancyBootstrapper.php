<?php

declare(strict_types=1);

namespace App\Tenancy\Bootstrappers;

use Stancl\Tenancy\Contracts\TenancyBootstrapper;
use Stancl\Tenancy\Contracts\Tenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Exceptions\TenantDatabaseDoesNotExistException;

class DatabaseTenancyBootstrapper implements TenancyBootstrapper
{
    public function __construct(
        protected DatabaseManager $database
    ) {}

    #[\Override]
    public function bootstrap(Tenant $tenant): void
    {
        /** @var TenantWithDatabase $tenant */

        // Better debugging, but breaks cached lookup in prod
        if (app()->environment('local')) {
            $this->database->createTenantConnection($tenant);

            $manager = $tenant->database()->manager();
            $manager->setConnection('tenant');

            if (! $manager->databaseExists($database = $tenant->database()->getName() ?? '')) {
                throw new TenantDatabaseDoesNotExistException($database);
            }
        }

        $this->database->connectToTenant($tenant);
    }

    #[\Override]
    public function revert(): void
    {
        $this->database->reconnectToCentral();
    }
}
