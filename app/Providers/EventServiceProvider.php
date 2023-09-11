<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Cart\Events\SanitizeCartEvent;
use Domain\Cart\Listeners\SanitizeCartListener;
use Domain\Order\Events\AdminOrderBankPaymentEvent;
use Domain\Order\Events\OrderPlacedEvent;
use Domain\Order\Events\AdminOrderStatusUpdatedEvent;
use Domain\Order\Events\OrderStatusUpdatedEvent;
use Domain\Order\Listeners\AdminOrderBankPaymentListener;
use Domain\Order\Listeners\OrderPlacedListener;
use Domain\Order\Listeners\AdminOrderStatusUpdatedListener;
use Domain\Order\Listeners\OrderPaymentUpdatedListener;
use Domain\Order\Listeners\OrderStatusUpdatedListener;
use Domain\Payments\Events\PaymentProcessEvent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        PaymentProcessEvent::class => [
            OrderPaymentUpdatedListener::class,
        ],
        OrderPlacedEvent::class => [
            OrderPlacedListener::class,
        ],
        AdminOrderStatusUpdatedEvent::class => [
            AdminOrderStatusUpdatedListener::class,
        ],
        AdminOrderBankPaymentEvent::class => [
            AdminOrderBankPaymentListener::class,
        ],
        OrderStatusUpdatedEvent::class => [
            OrderStatusUpdatedListener::class,
        ],
        SanitizeCartEvent::class => [
            SanitizeCartListener::class,
        ],
    ];

    /** Register any events for your application. */
    public function boot(): void
    {
    }

    /** Determine if events and listeners should be automatically discovered. */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
