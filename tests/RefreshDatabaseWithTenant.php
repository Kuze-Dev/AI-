<?php

declare(strict_types=1);

namespace Tests;

use Domain\Tenant\Database\Factories\TenantFactory;
use Domain\Tenant\Models\Tenant;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Foundation\Testing\RefreshDatabaseState;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\ParallelTesting;

/** https://discord.com/channels/976506366502006874/1341202555513995335/1341581357155094570 */
trait RefreshDatabaseWithTenant
{
    use LazilyRefreshDatabase;

    public Tenant $tenant;

    private const string TENANT_ID = 'tenant_id';

    //    public function getConnectionsToTransact(): array
    //    {
    //        return [null, 'tenant_template_sqlite'];
    //    }

    protected function refreshTestDatabase(): void
    {
        if (! RefreshDatabaseState::$migrated) {
            $this->artisan('migrate:fresh', $this->migrateFreshUsing());

            // $this->artisan('db:seed');

            $this->artisan('tenants:migrate-fresh');  // <--- added

            $this->app[Kernel::class]->setArtisan(null);

            RefreshDatabaseState::$migrated = true;
        }

        $this->beginDatabaseTransaction();
    }

    public function afterRefreshingDatabase(): void
    {
        config([
            'tenancy.database.prefix' => 'test_tenancy_'.(($token = ParallelTesting::token()) !== null ? $token.'_' : ''),
        ]);

        $dbName = config('tenancy.database.prefix').self::TENANT_ID.config('tenancy.database.suffix');

        File::delete(database_path($dbName));

        $this->tenant = TenantFactory::new()
            ->withDomains('foo.hasp.test')
            ->createOne([
                'id' => self::TENANT_ID,
                'name' => self::TENANT_ID,
            ]);

        $this->artisan('tenants:seed');
    }
}
