<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\DataTransferObjects\ServiceBillMilestonePipelineData;
use Domain\ServiceOrder\Enums\PaymentPlanValue;

class UpdateServiceBillMilestoneAction
{
    public function execute(ServiceBillMilestonePipelineData $serviceBillMilestonePipelineData): array
    {
        $serviceOrder = $serviceBillMilestonePipelineData->service_order;
        $paymentPlan = $serviceBillMilestonePipelineData->payment_plan;

        if ($serviceOrder->payment_value === PaymentPlanValue::PERCENT->value) {

            $totalAmount = $serviceOrder->total_price * ($paymentPlan['amount'] / 100);
            $taxTotal = $serviceOrder->tax_total * ($paymentPlan['amount'] / 100);

            return [
                'totalAmount' => $totalAmount,
                'taxTotal' => $taxTotal,
            ];
        }

        $totalAmount = $paymentPlan['amount'];
        $taxTotal = $paymentPlan['amount'] * ($serviceOrder->tax_percentage / 100);

        return [
            'totalAmount' => $totalAmount,
            'taxTotal' => $taxTotal,
        ];
    }
}
