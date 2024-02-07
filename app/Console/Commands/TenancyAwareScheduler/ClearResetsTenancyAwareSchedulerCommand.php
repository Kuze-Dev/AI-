<?php

declare(strict_types=1);

namespace App\Console\Commands\TenancyAwareScheduler;

use Illuminate\Auth\Console\ClearResetsCommand;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Stancl\Tenancy\Concerns\TenantAwareCommand;

class ClearResetsTenancyAwareSchedulerCommand extends ClearResetsCommand
{
    use HasATenantsOption;
    use TenantAwareCommand;

    protected $signature = 'app:tenants:auth:clear-resets {name? : The name of the password broker}';
}
