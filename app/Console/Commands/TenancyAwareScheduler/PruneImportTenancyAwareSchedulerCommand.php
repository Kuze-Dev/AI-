<?php

declare(strict_types=1);

namespace App\Console\Commands\TenancyAwareScheduler;

use HalcyonAgile\FilamentImport\Commands\PruneImportCommand;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Stancl\Tenancy\Concerns\TenantAwareCommand;

class PruneImportTenancyAwareSchedulerCommand extends PruneImportCommand
{
    use HasATenantsOption;
    use TenantAwareCommand;

    public $signature = 'app:tenants:import-prune';
}
