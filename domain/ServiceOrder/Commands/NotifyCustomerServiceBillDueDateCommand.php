<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Commands;

use Domain\ServiceOrder\Actions\NotifyCustomerServiceBillDueDateAction;
use Illuminate\Console\Command;
use Stancl\Tenancy\Concerns\HasATenantsOption;
use Stancl\Tenancy\Concerns\TenantAwareCommand;

class NotifyCustomerServiceBillDueDateCommand extends Command
{
    use HasATenantsOption;
    use TenantAwareCommand;

    /** @var string */
    protected $signature = 'notify:customer-service-bill-due-date-command';

    /** @var string */
    protected $description = 'Send email notification to customer (service bill due date).';

    public function handle(NotifyCustomerServiceBillDueDateAction $notifyCustomerServiceBillDueDateAction): int
    {
        $notifyCustomerServiceBillDueDateAction->execute();

        return self::SUCCESS;
    }
}
