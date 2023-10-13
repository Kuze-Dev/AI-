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

    public function __construct(private CreateServiceBillingsAction $createServiceBillingsAction)
    {
    }

    public function handle()
    {
        echo 'test';
        // $this->createServiceBillingsAction->execute();

        // return self::SUCCESS;
    }
}
