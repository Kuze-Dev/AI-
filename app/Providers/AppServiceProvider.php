<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Admin\Models\Admin;
use Domain\Blueprint\Models\Blueprint;
use Domain\Page\Models\Page;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Stancl\Tenancy\Database\Models\Tenant;

/** @property \Illuminate\Foundation\Application $app */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Model::shouldBeStrict( ! $this->app->isProduction());

        Model::handleMissingAttributeViolationUsing(function (Model $model, string $key) {
            if ($model instanceof Tenant && Str::startsWith($key, Tenant::internalPrefix())) {
                return null;
            }
        });

        Relation::enforceMorphMap([
            Admin::class,
            config('permission.models.role'),
            config('tenancy.tenant_model'),
            Blueprint::class,
            Page::class,
            Taxonomy::class,
            TaxonomyTerm::class,
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
