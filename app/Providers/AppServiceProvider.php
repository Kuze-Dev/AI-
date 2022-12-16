<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Admin\Models\Admin;
use Domain\Blueprint\Models\Blueprint;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Domain\Form\Models\FormSubmission;
use Domain\Page\Models\Page;
use Illuminate\Database\Eloquent\MassAssignmentException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Stancl\Tenancy\Database\Models\Tenant;
use TiMacDonald\JsonApi\JsonApiResource;

/** @property \Illuminate\Foundation\Application $app */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Model::shouldBeStrict( ! $this->app->isProduction());

        Model::handleDiscardedAttributeViolationUsing(function (Model $model, array $keys) {
            if (in_array($model->getKeyName(), $keys)) {
                unset($keys[array_search($model->getKeyName(), $keys)]);
            }

            if ( ! empty($keys)) {
                throw new MassAssignmentException(sprintf(
                    'Add fillable property [%s] to allow mass assignment on [%s].',
                    implode(', ', $keys),
                    $model::class
                ));
            }
        });

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
            Form::class,
            FormSubmission::class,
            FormEmailNotification::class,
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

        JsonApiResource::resolveIdUsing(fn (Model $resource): string => $resource->getRouteKey());
    }
}
