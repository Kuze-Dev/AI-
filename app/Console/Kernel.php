<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\CreateServiceBillCommand;
use App\Console\Commands\InactivateServiceOrderCommand;
use App\Console\Commands\NotifyCustomerServiceBillDueDateCommand;
use App\Console\Commands\TenancyAwareScheduler\ClearResetsTenancyAwareSchedulerCommand;
use App\Console\Commands\TenancyAwareScheduler\PruneExportTenancyAwareSchedulerCommand;
use App\Console\Commands\TenancyAwareScheduler\PruneImportTenancyAwareSchedulerCommand;
use App\Console\Commands\TenancyAwareScheduler\SanctumPruneExpiredTenancyAwareScheduler;
use HalcyonAgile\FilamentExport\Commands\PruneExportCommand;
use HalcyonAgile\FilamentImport\Commands\PruneImportCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /** Define the application's command schedule. */
    protected function schedule(Schedule $schedule): void
    {
        self::tenantsSchedules($schedule);

        $schedule->command(PruneExportCommand::class)
            ->daily();
        $schedule->command(PruneImportCommand::class)
            ->daily();

        // $schedule->command(DispatchQueueCheckJobsCommand::class)->everyMinute();

        // We recommend to put this command as the very last command in your schedule.
        // https://spatie.be/docs/laravel-health/available-checks/schedule
        // $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();

    }

    private static function tenantsSchedules(Schedule $schedule): void
    {
        $tenants = tenancy()->model()->cursor()->pluck('id')->toArray();

        $schedule->command(
            NotifyCustomerServiceBillDueDateCommand::class,
            ['--tenants' => $tenants]
        )
            ->daily()
            ->sentryMonitor();

        $schedule->command(
            CreateServiceBillCommand::class,
            ['--tenants' => $tenants]
        )
            ->daily()
            ->sentryMonitor();

        $schedule->command(
            InactivateServiceOrderCommand::class,
            ['--tenants' => $tenants]
        )
            ->daily()
            ->sentryMonitor();

        $schedule->command(
            PruneExportTenancyAwareSchedulerCommand::class,
            ['--tenants' => $tenants]
        )
            ->daily();
        $schedule->command(
            PruneImportTenancyAwareSchedulerCommand::class,
            ['--tenants' => $tenants]
        )
            ->daily();

        $schedule->command(
            ClearResetsTenancyAwareSchedulerCommand::class, [
                'customer',
                '--tenants' => $tenants,
            ])
            ->everyFifteenMinutes();

        $schedule->command(
            SanctumPruneExpiredTenancyAwareScheduler::class, [
                '--hours' => 24,
                '--tenants' => $tenants,
            ])
            ->daily();
    }

    /** Register the commands for the application. */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
