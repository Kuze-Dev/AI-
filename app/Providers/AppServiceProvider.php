<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Admin\Models\Admin;
use Domain\Blueprint\Models\Blueprint;
use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;
use Domain\Collection\Models\Collection;
use Domain\Collection\Models\CollectionEntry;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Domain\Form\Models\FormSubmission;
use Domain\Globals\Models\Globals;
use Domain\Page\Models\Page;
use Domain\Support\SlugHistory\SlugHistory;
use Domain\Page\Models\Slice;
use Domain\Support\MetaData\Models\MetaData;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
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
            Slice::class,
            Menu::class,
            Node::class,
            Form::class,
            FormSubmission::class,
            FormEmailNotification::class,
            Taxonomy::class,
            TaxonomyTerm::class,
            Collection::class,
            CollectionEntry::class,
            SlugHistory::class,
            Globals::class,
            MetaData::class,
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

        JsonApiResource::resolveIdUsing(fn (Model $resource): string => (string) $resource->getRouteKey());
    }
}
