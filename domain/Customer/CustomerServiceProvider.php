<?php

declare(strict_types=1);

namespace Domain\Customer;

use Domain\Customer\Events\PasswordResetSent;
use Domain\Customer\Listeners\LogPasswordReset;
use Domain\Customer\Listeners\LogPasswordResetSent;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Support\Providers\EventServiceProvider;

class CustomerServiceProvider extends EventServiceProvider
{
    /** @var array<class-string, array<int, class-string>> */
    protected $listen = [
        PasswordReset::class => [
            LogPasswordReset::class,
        ],
        PasswordResetSent::class => [
            LogPasswordResetSent::class,
        ],
    ];

    //    public function register(): void
    //    {
    //        parent::register();
    //
    //    }
}
