<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Domain\ServiceOrder\Models\ServiceBill;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\ServiceOrder\Notifications\ServiceBillPaidNotification;
use Illuminate\Support\Facades\Notification;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class SendToCustomerServiceTransactionReceiptEmailAction
{
    public function execute(
        ServiceOrder $serviceOrder,
        ServiceBill $serviceBill,
        Media $pdf
    ): void {
        Notification::route('mail', [
            $serviceOrder->customer_email => $serviceOrder->customer_full_name,
        ])->notify(
            new ServiceBillPaidNotification(
                $serviceOrder,
                $pdf
            )
        );
    }
}
