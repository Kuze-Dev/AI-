<?php

declare(strict_types=1);

namespace Domain\ServiceOrder;

use Illuminate\Support\ServiceProvider;

class ServiceOrderServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/config/service-order.php', 'domain.service-order');
    }
}
