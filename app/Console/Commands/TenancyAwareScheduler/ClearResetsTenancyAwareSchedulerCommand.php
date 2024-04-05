<?php

declare(strict_types=1);

namespace App\Console\Commands\TenancyAwareScheduler;

use Illuminate\Auth\Console\ClearResetsCommand;
use Illuminate\Support\Facades\DB;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Stancl\Tenancy\Concerns\TenantAwareCommand;

class ClearResetsTenancyAwareSchedulerCommand extends ClearResetsCommand
{
    use HasATenantsOption;
    use TenantAwareCommand;

    protected $signature = 'app:tenants:auth:clear-resets {name? : The name of the password broker}';

    public function handle(): int
    {
        // solve `Call to a member function prepare() on null`

        $driver = $this->argument('name') ?? config('auth.defaults.passwords');

        $table = config('auth.passwords.'.$driver.'.table');

        DB::table($table)
            ->where('created_at', '<', now()->addHour())->delete();

        $this->components->info('Expired reset tokens cleared successfully.');

        return self::SUCCESS;
    }
}
