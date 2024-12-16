<?php

declare(strict_types=1);

use App\Console\Commands\TenancyAwareScheduler\ClearResetsTenancyAwareSchedulerCommand;
use App\Console\Commands\TenancyAwareScheduler\SanctumPruneExpiredTenancyAwareScheduler;
use App\Http\Middleware\ApiCallTrackMiddleware;
use App\Http\Middleware\EnsureAccountIsActive;
use App\Http\Middleware\EnsureTenantFeaturesAreActive;
use App\Http\Middleware\EnsureTenantIsNotSuspended;
use Domain\ServiceOrder\Commands\CreateServiceBillCommand;
use Domain\ServiceOrder\Commands\InactivateServiceOrderCommand;
use Domain\ServiceOrder\Commands\NotifyCustomerServiceBillDueDateCommand;
use Filament\Facades\Filament;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Sentry\Laravel\Integration;
use Spatie\Health\Exceptions\CheckDidNotComplete;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Spatie\QueryBuilder\Exceptions\InvalidFilterValue;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        commands: __DIR__.'/../routes/console.php',
        then: function () {
            Route::middleware('web')
                ->group(function () {
                    Route::redirect('/', 'admin');
                });
        }
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware
            ->redirectGuestsTo(fn () => Filament::getLoginUrl())
            ->alias([
                'active' => EnsureAccountIsActive::class,
                'feature.tenant' => EnsureTenantFeaturesAreActive::class,
                'tenant.suspended' => EnsureTenantIsNotSuspended::class,
            ])
            ->group( 'universal', [])
            ->group( 'tenant', [
                InitializeTenancyByDomain::class,
                PreventAccessFromCentralDomains::class,
                EnsureTenantIsNotSuspended::class,
                ApiCallTrackMiddleware::class,
            ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        Integration::handles($exceptions);

        $exceptions
            ->dontReport([
                CheckDidNotComplete::class,
                FileIsTooBig::class,
                InvalidFilterValue::class,
            ])
            ->render(function (InvalidFilterValue $e, Request $request) {
                abort(400, $e->getMessage());
            });
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->command(NotifyCustomerServiceBillDueDateCommand::class)
            ->daily();

        $schedule->command(CreateServiceBillCommand::class)
            ->daily();

        $schedule->command(InactivateServiceOrderCommand::class)
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

        // $schedule->command(DispatchQueueCheckJobsCommand::class)->everyMinute();

        // We recommend to put this command as the very last command in your schedule.
        // https://spatie.be/docs/laravel-health/available-checks/schedule
        // $schedule->command(ScheduleCheckHeartbeatCommand::class)->everyMinute();
    })
    ->create();
