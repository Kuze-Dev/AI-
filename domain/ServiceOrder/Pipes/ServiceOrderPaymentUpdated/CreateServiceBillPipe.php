<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Pipes\ServiceOrderPaymentUpdated;

use Domain\ServiceOrder\Actions\GetServiceBillingAndDueDateAction;
use Domain\ServiceOrder\DataTransferObjects\GetServiceBillingAndDueData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderPaymentUpdatedPipelineData;
use Domain\ServiceOrder\Jobs\NotifyCustomerLatestServiceBillJob;

class CreateServiceBillPipe
{
    public function __construct(
        private readonly GetServiceBillingAndDueDateAction $getServiceBillingAndDueDateAction
    ) {}

    public function handle(
        ServiceOrderPaymentUpdatedPipelineData $serviceOrderPaymentUpdatedPipelineData,
        callable $next
    ): ServiceOrderPaymentUpdatedPipelineData {
        $serviceOrder = $serviceOrderPaymentUpdatedPipelineData->service_order;
        $serviceBill = $serviceOrderPaymentUpdatedPipelineData->service_bill;
        $serviceTransaction = $serviceOrderPaymentUpdatedPipelineData->service_transaction;

        $shouldCreateNewServiceBill = $serviceOrder->is_subscription &&
            ! $serviceOrder->is_auto_generated_bill &&
            ! $serviceOrderPaymentUpdatedPipelineData->is_service_order_status_closed;

        if (
            $serviceOrderPaymentUpdatedPipelineData->is_payment_paid &&
            $shouldCreateNewServiceBill
        ) {
            /** @var \Illuminate\Foundation\Bus\PendingDispatch $createServiceBillJob */
            $createServiceBillJob = dispatch(new \Domain\ServiceOrder\Jobs\CreateServiceBillJob($serviceOrder, $this->getServiceBillingAndDueDateAction
                ->execute(
                    new GetServiceBillingAndDueData(
                        service_order: $serviceOrder,
                        service_bill: $serviceBill,
                        service_transaction: $serviceTransaction
                    )
                )));

            $createServiceBillJob->chain([
                new NotifyCustomerLatestServiceBillJob($serviceOrder),
            ]);
        }

        return $next($serviceOrderPaymentUpdatedPipelineData);
    }
}
