<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\TenancyAwareScheduler\ClearResetsTenancyAwareSchedulerCommand;
use App\Console\Commands\TenancyAwareScheduler\PruneExportTenancyAwareSchedulerCommand;
use App\Console\Commands\TenancyAwareScheduler\PruneImportTenancyAwareSchedulerCommand;
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
        $schedule->command(NotifyCustomerServiceBillDueDateCommand::class)
            ->daily();

        $schedule->command(CreateServiceBillCommand::class)
            ->daily();

        $schedule->command(InactivateServiceOrderCommand::class)
            ->daily();

        $schedule->command(PruneExportTenancyAwareSchedulerCommand::class)
            ->daily();
        $schedule->command(PruneImportTenancyAwareSchedulerCommand::class)
            ->daily();

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
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
