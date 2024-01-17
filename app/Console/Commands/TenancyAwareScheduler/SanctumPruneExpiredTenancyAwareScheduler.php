<?php

declare(strict_types=1);

namespace App\Console\Commands\TenancyAwareScheduler;

use Laravel\Sanctum\Console\Commands\PruneExpired;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Stancl\Tenancy\Concerns\TenantAwareCommand;

class SanctumPruneExpiredTenancyAwareScheduler extends PruneExpired
{
    use HasATenantsOption;
    use TenantAwareCommand;

    protected $signature = 'app:tenants:sanctum:prune-expired {--hours=24 : The number of hours to retain expired Sanctum tokens}';
}
