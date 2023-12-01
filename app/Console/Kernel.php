<?php

declare(strict_types=1);

namespace App\Console;

use App\Console\Commands\CreateServiceBillCommand;
use App\Console\Commands\InactivateServiceOrderCommand;
use App\Console\Commands\NotifyCustomerServiceBillDueDateCommand;
use Domain\Tenant\Models\Tenant;
use HalcyonAgile\FilamentImport\Commands\PruneImportCommand;
use Illuminate\Auth\Console\ClearResetsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Laravel\Sanctum\Console\Commands\PruneExpired as SanctumPruneExpired;
use Spatie\Health\Commands\DispatchQueueCheckJobsCommand;
use Spatie\Health\Commands\ScheduleCheckHeartbeatCommand;
use Support\Excel\Commands\PruneExcelCommand;

class Kernel extends ConsoleKernel
{
    /** Define the application's command schedule. */
    protected function schedule(Schedule $schedule): void
    {
        $tenants = Tenant::pluck('id')->toArray();

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

        $schedule->command(PruneExcelCommand::class)
            ->daily();
        $schedule->command(PruneImportCommand::class)
            ->daily();

        $schedule->command(ClearResetsCommand::class, ['name' => 'customer'])
            ->everyFifteenMinutes();

        $schedule->command(SanctumPruneExpired::class, ['--hours' => 24])
            ->daily();

        // $schedule->command(DispatchQueueCheckJobsCommand::class)->everyMinute();

        // We recommend to put this command as the very last command in your schedule.
        // https://spatie.be/docs/laravel-health/available-checks/schedule
        // $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();

    }

    /** Register the commands for the application. */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
    }
}
