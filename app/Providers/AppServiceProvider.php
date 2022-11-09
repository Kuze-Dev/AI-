<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Admin\Models\Admin;
use Domain\Blueprint\Models\Blueprint;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

/** @property \Illuminate\Foundation\Application $app */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Model::preventLazyLoading( ! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes( ! $this->app->isProduction());

        Relation::enforceMorphMap([
            Admin::class,
            config('permission.models.role'),
            Blueprint::class,
        ]);

        Password::defaults(
            fn () => $this->app->environment('local', 'testing')
                ? Password::min(4)
                : Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols()
                    ->when(
                        $this->app->isProduction(),
                        fn (Password $password) => $password->uncompromised()
                    )
        );
    }
}
