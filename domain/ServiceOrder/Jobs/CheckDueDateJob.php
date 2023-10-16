<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Jobs;

use Domain\ServiceOrder\Actions\ExpiredServiceOrderAction;
use Domain\ServiceOrder\Models\ServiceBill;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CheckDueDateJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        private ServiceBill $serviceBill
    ) {
    }

    public function handle(ExpiredServiceOrderAction $expiredServiceOrderAction): void
    {
        $expiredServiceOrderAction
            ->execute(
                $this->serviceBill
            );
    }
}
