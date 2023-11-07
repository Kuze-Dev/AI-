<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Actions\SendToCustomerServiceOrderStatusEmailAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Notifications\ActivatedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ClosedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\CompletedServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ConfirmationServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ExpiredServiceOrderNotification;
use Domain\ServiceOrder\Notifications\ForPaymentNotification;
use Domain\ServiceOrder\Notifications\InProgressServiceOrderNotification;
use Illuminate\Support\Facades\Notification;

beforeEach(function () {
    testInTenantContext();

    Notification::fake();
});

it('can notify for active status', function () {

    app(SendToCustomerServiceOrderStatusEmailAction::class)
        ->execute(
            ServiceOrderFactory::new()
                ->active()
                ->for(CustomerFactory::new())
                ->has(ServiceBillFactory::new())
                ->createOne()
        );

    Notification::assertSentOnDemand(ActivatedServiceOrderNotification::class);
});

it('can notify for inactive status', function () {

    app(SendToCustomerServiceOrderStatusEmailAction::class)
        ->execute(
            ServiceOrderFactory::new()
                ->inactive()
                ->for(CustomerFactory::new())
                ->has(ServiceBillFactory::new())
                ->createOne()
        );

    Notification::assertSentOnDemand(ExpiredServiceOrderNotification::class);
});

it('can notify for closed status', function () {

    app(SendToCustomerServiceOrderStatusEmailAction::class)
        ->execute(
            ServiceOrderFactory::new()
                ->closed()
                ->for(CustomerFactory::new())
                ->has(ServiceBillFactory::new())
                ->createOne()
        );

    Notification::assertSentOnDemand(ClosedServiceOrderNotification::class);
});

it('can notify for payment status', function () {

    app(SendToCustomerServiceOrderStatusEmailAction::class)
        ->execute(
            ServiceOrderFactory::new()
                ->forPayment()
                ->for(CustomerFactory::new())
                ->has(ServiceBillFactory::new())
                ->createOne()
        );

    Notification::assertSentOnDemand(ForPaymentNotification::class);
});

it('can notify pending status', function () {

    app(SendToCustomerServiceOrderStatusEmailAction::class)
        ->execute(
            ServiceOrderFactory::new()
                ->pending()
                ->for(CustomerFactory::new())
                ->has(ServiceBillFactory::new())
                ->createOne()
        );

    Notification::assertSentOnDemand(ConfirmationServiceOrderNotification::class);
});

it('can notify in progress status', function () {

    app(SendToCustomerServiceOrderStatusEmailAction::class)
        ->execute(
            ServiceOrderFactory::new()
                ->inProgress()
                ->for(CustomerFactory::new())
                ->has(ServiceBillFactory::new())
                ->createOne()
        );

    Notification::assertSentOnDemand(InProgressServiceOrderNotification::class);
});

it('can notify completed status', function () {

    app(SendToCustomerServiceOrderStatusEmailAction::class)
        ->execute(
            ServiceOrderFactory::new()
                ->completed()
                ->for(CustomerFactory::new())
                ->has(ServiceBillFactory::new())
                ->createOne()
        );

    Notification::assertSentOnDemand(CompletedServiceOrderNotification::class);
});
