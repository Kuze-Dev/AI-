<?php

declare(strict_types=1);

namespace App\Providers;

use App\Settings\FormSettings;
use Domain\Admin\Models\Admin;
use Domain\Blueprint\Models\Blueprint;
use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Domain\Form\Models\FormSubmission;
use Domain\Globals\Models\Globals;
use Domain\Page\Models\Page;
use Domain\Page\Models\Block;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Support\Captcha\CaptchaManager;
use Domain\Support\MetaData\Models\MetaData;
use Domain\Support\Payments\Models\Payment;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Laravel\Pennant\Feature;
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

            throw new MissingAttributeException($model, $key);
        });

        Relation::enforceMorphMap([
            Admin::class,
            config('permission.models.role'),
            config('tenancy.tenant_model'),
            Blueprint::class,
            Page::class,
            Block::class,
            Menu::class,
            Node::class,
            Form::class,
            FormSubmission::class,
            FormEmailNotification::class,
            Taxonomy::class,
            TaxonomyTerm::class,
            Content::class,
            ContentEntry::class,
            Globals::class,
            MetaData::class,
            PaymentMethod::class,
            Payment::class,
        ]);

        Password::defaults(
            $this->app->environment('local', 'testing')
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

        CaptchaManager::resolveProviderUsing(
            fn () => tenancy()->initialized
                ? app(FormSettings::class)->provider
                : config('catpcha.provider')
        );

        CaptchaManager::resolveCredentialsUsing(
            fn () => tenancy()->initialized
                ? app(FormSettings::class)->getCredentials()
                : config('catpcha.credentials')
        );

        Feature::discover('App\\Features\\CMS', app_path('Features/CMS'));
        Feature::discover('App\\Features\\ECommerce', app_path('Features/ECommerce'));
    }
}
