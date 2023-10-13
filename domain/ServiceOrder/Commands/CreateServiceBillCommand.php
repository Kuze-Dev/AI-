<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Commands;

use Domain\ServiceOrder\Actions\CreateServiceBillingsAction;
use Illuminate\Console\Command;

class CreateServiceBillCommand extends Command
{
    /** @var string $signature*/
    protected $signature = 'app:create-service-bill-command';

    /** @var string $description*/
    protected $description = 'Generate service bill for customer';

    public function handle(CreateServiceBillingsAction $createServiceBillingsAction): int
    {
        $createServiceBillingsAction->execute();

        return self::SUCCESS;
    }
}
