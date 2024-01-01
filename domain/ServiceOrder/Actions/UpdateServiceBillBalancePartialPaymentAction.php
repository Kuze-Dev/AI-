<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\Payments\Models\Payment;
use Domain\ServiceOrder\Enums\ServiceBillStatus;
use Domain\ServiceOrder\Models\ServiceOrder;

class UpdateServiceBillBalancePartialPaymentAction
{
    public function execute(Payment $payment, ServiceOrder $serviceOrder): void
    {
        $bills = $serviceOrder->serviceBills()->whereStatus('pending')->get()->sortBy('created_at');

        $totalPayment = $payment->amount;

        foreach ($bills as $bill) {
            $billBalance = $bill->total_balance;

            $totalPayment -= $billBalance;

            if ($totalPayment >= 0) {
                $bill->update([
                    'status' => ServiceBillStatus::PAID,
                    'total_balance' => 0,
                ]);
            } else {
                $newBalance = abs($totalPayment);

                $bill->update([
                    'total_balance' => $newBalance,
                ]);
            }

            if ($totalPayment <= 0) {
                break;
            }
        }
    }
}
