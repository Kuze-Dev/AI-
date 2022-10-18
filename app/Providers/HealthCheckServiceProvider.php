<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Spatie\Health\Checks\Checks\CacheCheck;
use Spatie\Health\Checks\Checks\DatabaseCheck;
use Spatie\Health\Checks\Checks\DatabaseConnectionCountCheck;
use Spatie\Health\Checks\Checks\DebugModeCheck;
use Spatie\Health\Checks\Checks\EnvironmentCheck;
use Spatie\Health\Checks\Checks\OptimizedAppCheck;
use Spatie\Health\Checks\Checks\ScheduleCheck;
use Spatie\Health\Checks\Checks\UsedDiskSpaceCheck;
use Spatie\Health\Facades\Health;
use VictoRD11\SslCertificationHealthCheck\SslCertificationExpiredCheck;
use VictoRD11\SslCertificationHealthCheck\SslCertificationValidCheck;

/** @property \Illuminate\Foundation\Application $app */
class HealthCheckServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Health::checks([
            CacheCheck::new()->name('cache: default'),
            // https://spatie.be/docs/laravel-health/v1/available-checks/cpu-load
            //            CpuLoadCheck::new()
            //                ->failWhenLoadIsHigherInTheLast5Minutes(2.0)
            //                ->failWhenLoadIsHigherInTheLast15Minutes(1.5),
            DatabaseCheck::new(),
            DebugModeCheck::new(),
            EnvironmentCheck::new(),
            ScheduleCheck::new(),
            SslCertificationExpiredCheck::new()
                ->url(config('app.url'))
                ->warnWhenSslCertificationExpiringDay(24)
                ->failWhenSslCertificationExpiringDay(14),
            SslCertificationValidCheck::new()
                ->url(config('app.url')),
            UsedDiskSpaceCheck::new(),
            OptimizedAppCheck::new(),
            DatabaseConnectionCountCheck::new(),
        ]);
    }
}
