<?php

declare(strict_types=1);

namespace Domain\Customer;

use Illuminate\Support\ServiceProvider;

class CustomerServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/customer.php', 'domain.customer');
    }
}
