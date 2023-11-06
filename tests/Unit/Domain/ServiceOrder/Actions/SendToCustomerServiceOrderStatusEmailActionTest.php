<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Actions\SendToCustomerServiceOrderStatusEmailAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Notifications\ActivatedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ClosedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ExpiredServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ForPaymentNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    testInTenantContext();

    Notification::fake();
});

it('can notify for active status', function () {

    app(SendToCustomerServiceOrderStatusEmailAction::class)
        ->execute(
            $serviceOrder = ServiceOrderFactory::new()
                ->active()
                ->for(CustomerFactory::new())
                ->has(ServiceBillFactory::new())
                ->createOne()
        );

    Notification::assertSentTo(
        [$serviceOrder->customer],
        ActivatedServiceOrderNotification::class
    );
});

it('can notify for inactive status', function () {

    app(SendToCustomerServiceOrderStatusEmailAction::class)
        ->execute(
            $serviceOrder = ServiceOrderFactory::new()
                ->inactive()
                ->for(CustomerFactory::new())
                ->has(ServiceBillFactory::new())
                ->createOne()
        );

    Notification::assertSentTo(
        [$serviceOrder->customer],
        ExpiredServiceOrderNotification::class
    );
});

it('can notify for closed status', function () {

    app(SendToCustomerServiceOrderStatusEmailAction::class)
        ->execute(
            $serviceOrder = ServiceOrderFactory::new()
                ->closed()
                ->for(CustomerFactory::new())
                ->has(ServiceBillFactory::new())
                ->createOne()
        );

    Notification::assertSentTo(
        [$serviceOrder->customer],
        ClosedServiceOrderNotification::class
    );
});

it('can notify for payment status', function () {

    app(SendToCustomerServiceOrderStatusEmailAction::class)
        ->execute(
            $serviceOrder = ServiceOrderFactory::new()
                ->forPayment()
                ->for(CustomerFactory::new())
                ->has(ServiceBillFactory::new())
                ->createOne()
        );

    Notification::assertSentTo(
        [$serviceOrder->customer],
        ForPaymentNotification::class
    );
});
