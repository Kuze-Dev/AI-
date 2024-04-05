<?php

declare(strict_types=1);

namespace Domain\ServiceOrder;

use Domain\ServiceOrder\Commands\CreateServiceBillCommand;
use Domain\ServiceOrder\Commands\InactivateServiceOrderCommand;
use Domain\ServiceOrder\Commands\NotifyCustomerServiceBillDueDateCommand;
use Illuminate\Support\ServiceProvider;

class ServiceOrderServiceProvider extends ServiceProvider
{
    #[\Override]
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/config/service-order.php', 'domain.service-order');
    }

    public function boot(): void
    {
        $this->commands([
            CreateServiceBillCommand::class,
            InactivateServiceOrderCommand::class,
            NotifyCustomerServiceBillDueDateCommand::class,
        ]);
    }
}
