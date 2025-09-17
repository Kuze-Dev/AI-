<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\Str;
use Domain\Cart\Models\Cart;
use Domain\Form\Models\Form;
use Domain\Menu\Models\Menu;
use Domain\Menu\Models\Node;
use Domain\Page\Models\Page;
use Domain\Site\Models\Site;
use Domain\Tier\Models\Tier;
use Illuminate\Http\Request;
use Laravel\Pennant\Feature;
use Domain\Page\Models\Block;
use App\Settings\FormSettings;
use Domain\Admin\Models\Admin;
use Domain\Order\Models\Order;
use Illuminate\Validation\Rule;
use Sentry\Laravel\Integration;
use Domain\Address\Models\State;
use Domain\Cart\Models\CartLine;
use Domain\Review\Models\Review;
use Domain\Tenant\TenantSupport;
use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Content\Models\Content;
use Domain\Globals\Models\Globals;
use Domain\Order\Models\OrderLine;
use Domain\Product\Models\Product;
use Domain\Service\Models\Service;
use Domain\Payments\Models\Payment;
use Domain\Taxation\Models\TaxZone;
use Support\Captcha\CaptchaManager;
use Domain\Currency\Models\Currency;
use Domain\Customer\Models\Customer;
use Domain\Discount\Models\Discount;
use Domain\Favorite\Models\Favorite;
use Domain\Page\Models\BlockContent;
use Domain\Shipment\Models\Shipment;
use Domain\Taxonomy\Models\Taxonomy;
use Domain\Order\Models\OrderAddress;
use Support\MetaData\Models\MetaData;
use Domain\Blueprint\Models\Blueprint;
use Domain\Form\Models\FormSubmission;
use Domain\Tenant\Models\TenantApiKey;
use Illuminate\Validation\Rules\Email;
use Domain\Content\Models\ContentEntry;
use Domain\Shipment\Models\ShippingBox;
use Domain\Tenant\Models\TenantApiCall;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Domain\Taxonomy\Models\TaxonomyTerm;
use Illuminate\Cache\RateLimiting\Limit;
use TiMacDonald\JsonApi\JsonApiResource;
use Domain\Discount\Models\DiscountLimit;
use Domain\Payments\Models\PaymentRefund;
use Domain\Product\Models\ProductVariant;
use Illuminate\Validation\Rules\Password;
use Domain\Blueprint\Models\BlueprintData;
use Stancl\Tenancy\Database\Models\Tenant;
use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Support\Facades\RateLimiter;
use Domain\OpenAi\DocumentParser\DocxParser;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\Discount\Models\DiscountCondition;
use Domain\Form\Models\FormEmailNotification;
use Domain\Product\Models\ProductOptionValue;
use Domain\Internationalization\Models\Locale;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Discount\Models\DiscountRequirement;
use Domain\ShippingMethod\Models\ShippingMethod;
use Domain\ServiceOrder\Models\ServiceTransaction;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Database\Eloquent\Relations\Relation;
use Domain\OpenAi\Interfaces\DocumentParserInterface;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Filament\Actions\Exports\Downloaders\XlsxDownloader;
use Illuminate\Database\Eloquent\MissingAttributeException;
use Spatie\LaravelSettings\Console\CacheDiscoveredSettingsCommand;
use Spatie\LaravelSettings\Console\ClearDiscoveredSettingsCacheCommand;

/** @property \Illuminate\Foundation\Application $app */
class AppServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Model::shouldBeStrict($this->app->isLocal() || $this->app->runningUnitTests());

        Model::handleLazyLoadingViolationUsing(Integration::lazyLoadingViolationReporter());

        Model::handleMissingAttributeViolationUsing(function (Model $model, string $key) {
            if ($model instanceof Tenant && Str::startsWith($key, Tenant::internalPrefix())) {
                return null;
            }

            throw new MissingAttributeException($model, $key);
        });

        /** @phpstan-ignore argument.type */
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
            Media::class,
            TenantApiKey::class,
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

        Email::defaults(
            fn () => $this->app->environment('local', 'testing')
                ? Rule::email()
                : Rule::email()
                    ->rfcCompliant()
                    ->validateMxRecord()
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

        ThrottleRequests::shouldHashKeys(false);

        RateLimiter::for('api', function (Request $request) {
            if (
                $request->hasHeader('x-rate-key') &&
                $request->header('x-rate-key') === config()->string('custom.rate_limit_key')
            ) {
                return Limit::none();
            }

            /** @phpstan-ignore class.notFound */
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        $this->optimizes(
            optimize: CacheDiscoveredSettingsCommand::class,
            clear: ClearDiscoveredSettingsCacheCommand::class,
            key: 'settings',
        );

        $this->app->bind(XlsxDownloader::class, function () {
            return new \App\Filament\Actions\Exports\Downloaders\XlsxDownloader;
        });

        $this->app->bind(DocumentParserInterface::class, DocxParser::class);


    }
}
