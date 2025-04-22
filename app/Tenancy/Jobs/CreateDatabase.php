<?php

declare(strict_types=1);

namespace App\Tenancy\Jobs;

use Domain\Tenant\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Contracts\ManagesDatabaseUsers;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Events\CreatingDatabase;
use Stancl\Tenancy\Events\DatabaseCreated;
use Stancl\Tenancy\Exceptions\TenantDatabaseAlreadyExistsException;
use Stancl\Tenancy\Exceptions\TenantDatabaseUserAlreadyExistsException;

class CreateDatabase implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Tenant $tenant
    ) {}

    public function handle(DatabaseManager $databaseManager): ?bool
    {

        $is_create_database = $this->tenant->getInternal('create_database') ? true : false;
        // Terminate execution of this job & other jobs in the pipeline
        if ($is_create_database === false) {
            return false;
        }

        $this->tenant->database()->makeCredentials();
        $databaseManager->createTenantConnection($this->tenant);

        $manager = $this->tenant->database()->manager();
        $manager->setConnection('tenant');

        if ($manager->databaseExists($database = $this->tenant->database()->getName() ?? '')) {
            $migrationRepository = app('migration.repository');
            $migrationRepository->setSource('tenant');

            if ($migrationRepository->repositoryExists()) {
                throw new TenantDatabaseAlreadyExistsException($database);
            }

            return false;
        }

        if ($manager instanceof ManagesDatabaseUsers && $manager->userExists($username = $this->tenant->database()->getUsername() ?? '')) {
            throw new TenantDatabaseUserAlreadyExistsException($username);
        }

        event(new CreatingDatabase($this->tenant));

        $manager->createDatabase($this->tenant);

        event(new DatabaseCreated($this->tenant));

        return true;
    }
}
