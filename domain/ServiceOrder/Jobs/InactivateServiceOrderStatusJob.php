<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Jobs;

use Domain\ServiceOrder\Actions\UpdateServiceOrderStatusAction;
use Domain\ServiceOrder\DataTransferObjects\UpdateServiceOrderStatusData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class InactivateServiceOrderStatusJob implements ShouldBeUnique, ShouldQueue
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

    public function handle(UpdateServiceOrderStatusAction $updateServiceOrderStatusAction): void
    {
        $updateServiceOrderStatusAction->execute(
            $this->serviceOrder,
            new UpdateServiceOrderStatusData(
                status: ServiceOrderStatus::INACTIVE
            )
        );

        Log::info('Inactivated Service Order: '.$this->serviceOrder->getRouteKey());
    }
}
