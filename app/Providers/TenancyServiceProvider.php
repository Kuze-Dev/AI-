<?php

declare(strict_types=1);

namespace App\Providers;

use App\Tenancy\Jobs\CreateDatabase;
use App\Tenancy\Jobs\CreateS3Bucket;
use App\Tenancy\Jobs\DeleteS3Bucket;
use Domain\Tenant\Models\Tenant;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Stancl\JobPipeline\JobPipeline;
use Stancl\Tenancy\Controllers\TenantAssetsController;
use Stancl\Tenancy\DatabaseConfig;
use Stancl\Tenancy\Events;
use Stancl\Tenancy\Features\TenantConfig;
use Stancl\Tenancy\Jobs;
use Stancl\Tenancy\Listeners;
use Stancl\Tenancy\Middleware;

/** @property \Illuminate\Foundation\Application $app */
class TenancyServiceProvider extends ServiceProvider
{
    /** @return array<class-string, array<mixed>> */
    public function events(): array
    {
        return [
            // Tenant events
            Events\CreatingTenant::class => [],
            Events\TenantCreated::class => [
                function (Events\TenantCreated $event) {
                    /** @var Tenant $tenant */
                    $tenant = $event->tenant;

                    Bus::chain(array_merge(
                        [
                            new CreateDatabase($tenant),
                            new Jobs\MigrateDatabase($tenant),
                        ],
                        ! app()->runningUnitTests()
                            ? [
                                new Jobs\SeedDatabase($tenant),
                                new CreateS3Bucket($tenant),
                            ]
                            : [],
                    ))->dispatch();
                },
            ],
            Events\SavingTenant::class => [],
            Events\TenantSaved::class => [],
            Events\UpdatingTenant::class => [],
            Events\TenantUpdated::class => [],
            Events\DeletingTenant::class => [],
            Events\TenantDeleted::class => [
                JobPipeline::make([
                    Jobs\DeleteDatabase::class,
                    DeleteS3Bucket::class,
                ])->send(function (Events\TenantDeleted $event) {
                    return $event->tenant;
                })->shouldBeQueued($this->app->isProduction()),
            ],

            // Domain events
            Events\CreatingDomain::class => [],
            Events\DomainCreated::class => [],
            Events\SavingDomain::class => [],
            Events\DomainSaved::class => [],
            Events\UpdatingDomain::class => [],
            Events\DomainUpdated::class => [],
            Events\DeletingDomain::class => [],
            Events\DomainDeleted::class => [],

            // Database events
            Events\DatabaseCreated::class => [],
            Events\DatabaseMigrated::class => [],
            Events\DatabaseSeeded::class => [],
            Events\DatabaseRolledBack::class => [],
            Events\DatabaseDeleted::class => [],

            // Tenancy events
            Events\InitializingTenancy::class => [],
            Events\TenancyInitialized::class => [
                Listeners\BootstrapTenancy::class,
            ],

            Events\EndingTenancy::class => [],
            Events\TenancyEnded::class => [
                Listeners\RevertToCentralContext::class,
                function (Events\TenancyEnded $event) {
                    config(['permission.cache.key' => 'spatie.permission.cache']);
                },
            ],

            Events\BootstrappingTenancy::class => [],
            Events\TenancyBootstrapped::class => [
                function (Events\TenancyBootstrapped $event) {
                    if ($event->tenancy->tenant) {
                        config(['permission.cache.key' => 'spatie.permission.cache.tenant.'.$event->tenancy->tenant->getAttribute('id')]);
                    }
                },
            ],
            Events\RevertingToCentralContext::class => [],
            Events\RevertedToCentralContext::class => [],

            // Resource syncing
            Events\SyncedResourceSaved::class => [
                Listeners\UpdateSyncedResource::class,
            ],

            // Fired only when a synced resource is changed in a different DB than the origin DB (to avoid infinite loops)
            Events\SyncedResourceChangedInForeignDatabase::class => [],
        ];
    }

    public function register(): void
    {
    }

    public function boot(): void
    {
        $this->bootEvents();

        $this->makeTenancyMiddlewareHighestPriority();

        TenantConfig::$storageToConfigMap = [
            'name' => [
                'app.name',
                'filament.brand',
            ],
        ];

        DatabaseConfig::generateDatabaseNamesUsing(fn (Tenant $tenant) => config('tenancy.database.prefix').Str::of($tenant->name)->lower()->snake().config('tenancy.database.suffix'));

        TenantAssetsController::$tenancyMiddleware = 'tenant';
    }

    protected function bootEvents(): void
    {
        foreach ($this->events() as $event => $listeners) {
            foreach ($listeners as $listener) {
                if ($listener instanceof JobPipeline) {
                    $listener = $listener->toListener();
                }

                Event::listen($event, $listener);
            }
        }
    }

    protected function makeTenancyMiddlewareHighestPriority(): void
    {
        $tenancyMiddleware = [
            // Even higher priority than the initialization middleware
            Middleware\PreventAccessFromCentralDomains::class,

            Middleware\InitializeTenancyByDomain::class,
            Middleware\InitializeTenancyBySubdomain::class,
            Middleware\InitializeTenancyByDomainOrSubdomain::class,
            Middleware\InitializeTenancyByPath::class,
            Middleware\InitializeTenancyByRequestData::class,
        ];

        foreach (array_reverse($tenancyMiddleware) as $middleware) {
            $this->app[\Illuminate\Contracts\Http\Kernel::class]->prependToMiddlewarePriority($middleware);
        }
    }
}
