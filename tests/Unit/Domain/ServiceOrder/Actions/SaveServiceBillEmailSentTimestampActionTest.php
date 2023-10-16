<?php

declare(strict_types=1);

use Domain\ServiceOrder\Actions\SaveServiceBillEmailSentTimestampAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;

beforeEach(function () {
    testInTenantContext();
});

it('can update', function () {
    $serviceBill = ServiceBillFactory::new()->createOne();

    app(SaveServiceBillEmailSentTimestampAction::class)->execute($serviceBill);

    expect($serviceBill->email_notification_sent_at)->not->toBeNull();
});
