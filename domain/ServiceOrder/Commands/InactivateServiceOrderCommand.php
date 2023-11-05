<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Commands;

use Domain\ServiceOrder\Actions\InactivateServiceOrdersAction;
use Illuminate\Console\Command;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Stancl\Tenancy\Concerns\TenantAwareCommand;

class InactivateServiceOrderCommand extends Command
{
    use HasATenantsOption;
    use TenantAwareCommand;

    /** @var string */
    protected $signature = 'app:inactivate-service-order-command';

    /** @var string */
    protected $description = 'Inactivate service orders';

    public function handle(InactivateServiceOrdersAction $inactivateServiceOrdersAction): int
    {
        $inactivateServiceOrdersAction->execute();

        return self::SUCCESS;
    }
}
