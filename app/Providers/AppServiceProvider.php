<?php

declare(strict_types=1);

namespace App\Providers;

use App\Settings\FormSettings;
use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Admin\Models\Admin;
use Domain\Blueprint\Models\Blueprint;
use Domain\Blueprint\Models\BlueprintData;
use Domain\Cart\Models\Cart;
use Domain\Cart\Models\CartLine;
use Domain\Content\Models\Content;
use Domain\Content\Models\ContentEntry;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Discount\Models\Discount;
use Domain\Discount\Models\DiscountCondition;
use Domain\Discount\Models\DiscountLimit;
use Domain\Discount\Models\DiscountRequirement;
use Domain\Favorite\Models\Favorite;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;
use Domain\Form\Models\FormSubmission;
use Domain\Globals\Models\Globals;
use Domain\Internationalization\Models\Locale;
use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;
use Domain\Order\Models\Order;
use Domain\Order\Models\OrderAddress;
use Domain\Order\Models\OrderLine;
use Domain\Page\Models\Block;
use Domain\Page\Models\BlockContent;
use Domain\Page\Models\Page;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Models\Payment;
use Domain\Payments\Models\PaymentRefund;
use Domain\Product\Models\Product;
use Domain\Product\Models\ProductOptionValue;
use Domain\Product\Models\ProductVariant;
use Domain\Review\Models\Review;
use Domain\Service\Models\Service;
use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Domain\Shipment\Models\Shipment;
use Domain\Shipment\Models\ShippingBox;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\Site\Models\Site;
use Domain\Taxation\Models\TaxZone;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Domain\Tenant\Models\TenantApiCall;
use Domain\Tenant\TenantSupport;
use Domain\Tier\Models\Tier;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Laravel\Pennant\Feature;
use Sentry\Laravel\Integration;
use Stancl\Tenancy\Database\Models\Tenant;
use Support\Captcha\CaptchaManager;
use Support\MetaData\Models\MetaData;
use TiMacDonald\JsonApi\JsonApiResource;

/** @property \Illuminate\Foundation\Application $app */
class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
    }

    public function boot(): void
    {
        Model::shouldBeStrict($this->app->isLocal() || $this->app->runningUnitTests());

        // Model::handleLazyLoadingViolationUsing(Integration::lazyLoadingViolationReporter());

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
            Product::class,
            ProductVariant::class,
            Taxonomy::class,
            TaxonomyTerm::class,
            Content::class,
            ContentEntry::class,
            Globals::class,
            MetaData::class,
            BlockContent::class,
            BlueprintData::class,
            Discount::class,
            DiscountRequirement::class,
            DiscountCondition::class,
            DiscountLimit::class,
            TaxZone::class,
            PaymentMethod::class,
            Payment::class,
            Tier::class,
            Customer::class,
            Address::class,
            Country::class,
            State::class,
            Currency::class,
            Tier::class,
            Customer::class,
            Address::class,
            Cart::class,
            CartLine::class,
            PaymentMethod::class,
            Payment::class,
            Order::class,
            OrderLine::class,
            OrderAddress::class,
            Favorite::class,
            Review::class,
            Shipment::class,
            ShippingMethod::class,
            ShippingBox::class,
            PaymentRefund::class,
            Locale::class,
            Site::class,
            Service::class,
            ServiceOrder::class,
            ServiceBill::class,
            TenantApiCall::class,
            ProductOptionValue::class,
            ServiceTransaction::class,
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

        Rule::macro(
            'email',
            fn (): string => app()->isProduction()
                ? 'email:rfc,dns'
                : 'email'
        );

        JsonApiResource::resolveIdUsing(fn (Model $resource): string => (string) $resource->getRouteKey());

        CaptchaManager::resolveProviderUsing(
            fn () => TenantSupport::initialized()
                ? app(FormSettings::class)->provider
                : config('catpcha.provider')
        );

        CaptchaManager::resolveCredentialsUsing(
            fn () => TenantSupport::initialized()
                ? app(FormSettings::class)->getCredentials()
                : config('catpcha.credentials')
        );

        Feature::useMorphMap();
        Feature::discover('App\\Features\\CMS', app_path('Features/CMS'));
        Feature::discover('App\\Features\\ECommerce', app_path('Features/ECommerce'));
        Feature::discover('App\\Features\\Customer', app_path('Features/Customer'));
        Feature::discover('App\\Features\\Service', app_path('Features/Service'));
        Feature::discover('App\\Features\\Shopconfiguration', app_path('Features/Shopconfiguration'));
        Feature::discover('App\\Features\\Shopconfiguration\PaymentGateway', app_path('Features/Shopconfiguration/PaymentGateway'));
        Feature::discover('App\\Features\\Shopconfiguration\Shipping', app_path('Features/Shopconfiguration/Shipping'));
    }
}
