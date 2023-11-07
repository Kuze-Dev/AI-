<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderCreated;

use Domain\ServiceOrder\Actions\SendToCustomerServiceBillEmailAction;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderCreatedPipelineData;
use Domain\ServiceOrder\Jobs\NotifyCustomerServiceOrderStatusJob;

class NotifyCustomerPipe
{
    public function __construct(
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
            /** @var \Domain\Customer\Models\Customer $customer */
            $customer = $serviceOrderCreatedPipelineData
                ->serviceOrder
                ->customer;

            NotifyCustomerServiceOrderStatusJob::dispatch($serviceOrderCreatedPipelineData->serviceOrder);

            $this->sendToCustomerServiceBillEmailAction
                ->execute(
                    $customer,
                    $latestServiceBill
                );
        }

        return $next($serviceOrderCreatedPipelineData);
    }
}
