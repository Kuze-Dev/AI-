<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderPaymentUpdated;

use Domain\ServiceOrder\Actions\UpdateServiceOrderStatusAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderPaymentUpdatedPipelineData;
use Domain\ServiceOrder\DataTransferObjects\UpdateServiceOrderStatusData;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Jobs\NotifyCustomerServiceOrderStatusJob;

class UpdateServiceOrderStatusPipe
{
    public function __construct(
        private UpdateServiceOrderStatusAction $updateServiceOrderStatusAction
    ) {
    }

    public function handle(
        ServiceOrderPaymentUpdatedPipelineData $serviceOrderPaymentUpdatedPipelineData,
        callable $next
    ): ServiceOrderPaymentUpdatedPipelineData {
        $serviceOrder = $serviceOrderPaymentUpdatedPipelineData->service_order;

        if (
            $serviceOrderPaymentUpdatedPipelineData->is_payment_paid &&
            ! $serviceOrderPaymentUpdatedPipelineData->is_service_order_status_closed
        ) {
            $serviceOrder = $this->updateServiceOrderStatusAction->execute(
                $serviceOrder,
                new UpdateServiceOrderStatusData(
                    service_order_status: $serviceOrder->is_subscription
                        ? ServiceOrderStatus::ACTIVE
                        : ServiceOrderStatus::INPROGRESS
                )
            );

            NotifyCustomerServiceOrderStatusJob::dispatch($serviceOrder);
        }

        return $next($serviceOrderPaymentUpdatedPipelineData);
    }
}
