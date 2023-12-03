<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\GenerateServiceTransactionReceiptAction;
use Domain\ServiceOrder\Actions\SendToCustomerServiceTransactionReceiptEmailAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Database\Factories\ServiceTransactionFactory;
use Domain\ServiceOrder\Notifications\ServiceBillPaidNotification;
use Illuminate\Support\Facades\Notification;

it('can execute', function () {
    testInTenantContext();

    Notification::fake();

    $pdf = app(GenerateServiceTransactionReceiptAction::class)
        ->execute(ServiceTransactionFactory::new()->createOne());

    app(SendToCustomerServiceTransactionReceiptEmailAction::class)
        ->execute(
            ServiceOrderFactory::new()->createOne(),
            ServiceBillFactory::new()->createOne(),
            $pdf
        );

    Notification::assertSentOnDemand(ServiceBillPaidNotification::class);
});
