<?php

declare(strict_types=1);

namespace Domain\Support\RouteUrl;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use ReflectionException;

class RouteUrlServiceProvider extends ServiceProvider
{
    /** @throws ReflectionException */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            Blueprint::mixin(new BluePrintMixin());
        }

        Event::subscribe(RouteUrlEventSubscriber::class);
    }
}
