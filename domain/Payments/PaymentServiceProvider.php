<?php

declare(strict_types=1);

namespace Domain\Payments;

use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Providers\OfflinePayment;
use Domain\Payments\Providers\PaypalProvider;
use Domain\Payments\Providers\StripeProvider;
use Domain\Payments\Providers\VisionPayProvider;
use Domain\Tenant\TenantSupport;
use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

class PaymentServiceProvider extends ServiceProvider implements DeferrableProvider
{
    #[\Override]
    public function register()
    {
        $this->app->singleton(
            PaymentManagerInterface::class,
            fn ($app) => $app->make(PaymentManager::class)
        );

        $this->mergeConfigFrom(__DIR__.'/config/payment.php', 'payment-gateway');
    }

    public function boot(): void
    {
        if (TenantSupport::initialized()) {

            $paymentMethods = PaymentMethod::all();

            if ($paymentMethods->count() > 0) {
                foreach ($paymentMethods as $paymentType) {
                    app(PaymentManagerInterface::class)->extend($paymentType->slug, fn () => match ($paymentType->gateway) {
                        'paypal' => new PaypalProvider(),
                        'manual' => new OfflinePayment(),
                        'stripe' => new StripeProvider(),
                        'bank-transfer' => new OfflinePayment(),
                        'vision-pay' => new VisionPayProvider(),
                        default => throw new InvalidArgumentException(),
                    });
                }
            }
        }

    }

    #[\Override]
    public function provides()
    {

        return [
            PaymentManagerInterface::class,
        ];
    }
}
