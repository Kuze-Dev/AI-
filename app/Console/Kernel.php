<?php

declare(strict_types=1);

namespace App\Console;

use Domain\ServiceOrder\Commands\CreateServiceBillCommand;
use Domain\ServiceOrder\Commands\NotifyCustomerServiceBillDueDateCommand;
use Illuminate\Auth\Console\ClearResetsCommand;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Laravel\Sanctum\Console\Commands\PruneExpired as SanctumPruneExpired;
use Support\Excel\Commands\PruneExcelCommand;

class Kernel extends ConsoleKernel
{
    /** Define the application's command schedule. */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command(NotifyCustomerServiceBillDueDateCommand::class)
            ->daily()
            ->sentryMonitor();

        // $schedule->command(CreateServiceBillCommand::class)
        //     ->daily()
        //     ->sentryMonitor();

        $schedule->command(PruneExcelCommand::class)
            ->daily();

        $schedule->command(ClearResetsCommand::class, ['name' => 'customer'])
            ->everyFifteenMinutes();

        $schedule->command(SanctumPruneExpired::class, ['--hours' => 24])
            ->daily();
    }

    /** Register the commands for the application. */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
    }
}
