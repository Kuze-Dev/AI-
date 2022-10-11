<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

/** @property \Illuminate\Foundation\Application $app */
class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Model::preventLazyLoading( ! $this->app->isProduction());
        Model::preventSilentlyDiscardingAttributes( ! $this->app->isProduction());

        Relation::enforceMorphMap([
            'admin' => \Domain\Admin\Models\Admin::class,
        ]);

        Password::defaults(
            fn () => Password::min(8)
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
