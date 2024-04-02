<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /** Define your route model bindings, pattern filters, and other route configuration. */
    #[\Override]
    public function boot(): void
    {
        $this->configureRateLimiting();

        $this->routes(function () {
            Route::middleware('web')
                ->group(function () {
                    Route::redirect('/', '/admin');
                });
        });
    }

    /** Configure the rate limiters for the application. */
    protected function configureRateLimiting(): void
    {
        RateLimiter::for('api', function (Request $request) {
            if ($request->hasHeader('x-rate-key')) {

                $ratekey = $request->header('x-rate-key');

                if ($ratekey === config('custom.rate_limit_key')) {

                    return Limit::none();
                }
            }

            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());

        });
    }
}
