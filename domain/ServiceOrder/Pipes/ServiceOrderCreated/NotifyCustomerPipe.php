<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderCreated;

use Domain\ServiceOrder\Actions\SendToCustomerServiceBillEmailAction;
use Domain\ServiceOrder\Actions\SendToCustomerServiceOrderStatusEmailAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderCreatedPipelineData;

class NotifyCustomerPipe
{
    public function __construct(
        private SendToCustomerServiceOrderStatusEmailAction $sendToCustomerServiceOrderStatusEmailAction,
        private SendToCustomerServiceBillEmailAction $sendToCustomerServiceBillEmailAction,
    ) {
    }

    public function handle(
        ServiceOrderCreatedPipelineData $serviceOrderCreatedPipelineData,
        callable $next
    ): ServiceOrderCreatedPipelineData {

        if (
            $latestServiceBill = $serviceOrderCreatedPipelineData
                ->serviceOrder
                ->latestServiceBill()
        ) {

            $this->sendToCustomerServiceOrderStatusEmailAction
                ->execute($serviceOrderCreatedPipelineData->serviceOrder);

            $this->sendToCustomerServiceBillEmailAction
                ->execute(
                    /** @phpstan-ignore-next-line */
                    $serviceOrderCreatedPipelineData
                        ->serviceOrder
                        ->customer,
                    $latestServiceBill
                );
        }

        return $next($serviceOrderCreatedPipelineData);
    }
}
