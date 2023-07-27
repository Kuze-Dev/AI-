<?php

declare(strict_types=1);

namespace App\Providers;

use Domain\Order\Events\OrderPlacedEvent;
use Domain\Order\Events\OrderStatusUpdatedEvent;
use Domain\Order\Listeners\OrderPlacedListener;
use Domain\Order\Listeners\OrderStatusUpdatedListener;
use Domain\Order\Listeners\OrderUpdatedListener;
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
            OrderUpdatedListener::class,
        ],
        OrderPlacedEvent::class => [
            OrderPlacedListener::class,
        ],
        OrderStatusUpdatedEvent::class => [
            OrderStatusUpdatedListener::class,
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
