<?php

declare(strict_types=1);

namespace App\Console\Commands\TenancyAwareScheduler;

use HalcyonAgile\FilamentExport\Commands\PruneExportCommand;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Stancl\Tenancy\Concerns\TenantAwareCommand;

class PruneExportTenancyAwareSchedulerCommand extends PruneExportCommand
{
    use HasATenantsOption;
    use TenantAwareCommand;

    protected $signature = 'app:tenants:export-prune';
}
