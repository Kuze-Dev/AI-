<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Jobs;

use Domain\ServiceOrder\Actions\SendToCustomerServiceOrderStatusEmailAction;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class NotifyCustomerServiceOrderStatusJob implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(private ServiceOrder $serviceOrder) {}

    public function uniqueId(): string
    {
        return $this->serviceOrder->reference;
    }

    public function handle(
        SendToCustomerServiceOrderStatusEmailAction $sendToCustomerServiceOrderStatusEmailAction
    ): void {
        $sendToCustomerServiceOrderStatusEmailAction->execute($this->serviceOrder);
    }
}
