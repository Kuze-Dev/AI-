<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Commands;

use Domain\ServiceOrder\Actions\CheckDueDateAction;
use Illuminate\Console\Command;

class CheckDueDateCommand extends Command
{
    /** @var string */
    protected $signature = 'app:closed-service-bill-command';

    /** @var string */
    protected $description = 'Generate service bill for customer';

    public function handle(CheckDueDateAction $checkDueDateAction): int
    {
        $checkDueDateAction->execute();

        return self::SUCCESS;
    }
}
