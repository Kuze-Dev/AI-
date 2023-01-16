<?php

declare(strict_types=1);

namespace App\Tenancy\Jobs;

use Domain\Tenant\Models\Tenant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Migrations\MigrationRepositoryInterface;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Events\CreatingDatabase;
use Stancl\Tenancy\Events\DatabaseCreated;
use Stancl\Tenancy\Exceptions\TenantDatabaseAlreadyExistsException;

class CreateDatabase implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected Tenant $tenant
    ) {
        $this->tenant = $tenant;
    }

    public function handle(DatabaseManager $databaseManager, MigrationRepositoryInterface $repository): ?bool
    {
        // Terminate execution of this job & other jobs in the pipeline
        if ($this->tenant->getInternal('create_database') === false) {
            return false;
        }

        $this->tenant->database()->makeCredentials();

        try {
            $databaseManager->ensureTenantCanBeCreated($this->tenant);
        } catch (TenantDatabaseAlreadyExistsException $exception) {
            if ($this->tenant->run(fn () => $repository->repositoryExists())) {
                throw $exception;
            }

            return false;
        }

        event(new CreatingDatabase($this->tenant));

        $this->tenant->database()->manager()->createDatabase($this->tenant);

        event(new DatabaseCreated($this->tenant));

        return true;
    }
}
