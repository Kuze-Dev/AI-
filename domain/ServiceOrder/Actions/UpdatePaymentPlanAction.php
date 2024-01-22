<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillMilestonePipelineData;

class UpdatePaymentPlanAction
{
    public function execute(ServiceBillMilestonePipelineData $serviceBillMilestonePipelineData): void
    {
        $serviceOrder = $serviceBillMilestonePipelineData->service_order;
        $paymentPlan = $serviceBillMilestonePipelineData->payment_plan;

        $data = $serviceOrder->payment_plan;
        $key = array_search($paymentPlan['description'], array_column($data, 'description'));

        if ($key !== false) {
            $data[$key]['is_generated'] = true;

            $serviceOrder->payment_plan = $data;
            $serviceOrder->save();
        }
    }
}
