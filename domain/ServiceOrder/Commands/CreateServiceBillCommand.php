<?php

namespace Domain\ServiceOrder\Commands;

use Domain\ServiceOrder\Actions\CreateServiceBillingsAction;
use Illuminate\Console\Command;

class CreateServiceBillCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:create-service-bill-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate service bill for customer';

    public function handle(CreateServiceBillingsAction $createServiceBillingsAction)
    {
        $createServiceBillingsAction->execute();

        return self::SUCCESS;
    }
}
