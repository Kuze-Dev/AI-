<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\TenancyAwareScheduler\ClearResetsTenancyAwareSchedulerCommand;
use App\Console\Commands\TenancyAwareScheduler\SanctumPruneExpiredTenancyAwareScheduler;
use Domain\ServiceOrder\Commands\CreateServiceBillCommand;
use Domain\ServiceOrder\Commands\InactivateServiceOrderCommand;
use Domain\ServiceOrder\Commands\NotifyCustomerServiceBillDueDateCommand;
use HalcyonAgile\FilamentExport\Commands\PruneExportCommand;
use HalcyonAgile\FilamentImport\Commands\PruneImportCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /** Define the application's command schedule. */
    #[\Override]
    protected function schedule(Schedule $schedule): void
    {
        self::tenantsSchedules($schedule);

        // $schedule->command(DispatchQueueCheckJobsCommand::class)->everyMinute();

        // We recommend to put this command as the very last command in your schedule.
        // https://spatie.be/docs/laravel-health/available-checks/schedule
        // $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();

    }

    private static function tenantsSchedules(Schedule $schedule): void
    {
        $schedule->command(NotifyCustomerServiceBillDueDateCommand::class)
            ->daily()
            ->sentryMonitor();

        $schedule->command(CreateServiceBillCommand::class)
            ->daily()
            ->sentryMonitor();

        $schedule->command(InactivateServiceOrderCommand::class)
            ->daily()
            ->sentryMonitor();

        $schedule->command(
            ClearResetsTenancyAwareSchedulerCommand::class, [
                'customer',
            ])
            ->everyFifteenMinutes();

        $schedule->command(
            SanctumPruneExpiredTenancyAwareScheduler::class, [
                '--hours' => 24,
            ])
            ->daily();
    }

    /** Register the commands for the application. */
    #[\Override]
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
