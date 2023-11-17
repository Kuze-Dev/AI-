<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Illuminate\Support\Facades\Log;

class InactivateServiceOrderStatusAction
{
    public function execute(ServiceOrder $serviceOrder): ServiceOrder
    {
        $serviceOrder->update(['status' => ServiceOrderStatus::INACTIVE]);

        Log::info('Inactivated Service Order: '.$serviceOrder->getRouteKey());

        return $serviceOrder;
    }
}
