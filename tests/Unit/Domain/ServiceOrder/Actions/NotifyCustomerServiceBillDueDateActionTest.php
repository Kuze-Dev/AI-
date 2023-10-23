<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\ServiceOrder\Actions\ExpiredServiceOrderAction;
use Domain\ServiceOrder\Actions\NotifyCustomerServiceBillDueDateAction;
use Domain\ServiceOrder\Actions\SendToCustomerServiceBillDueDateEmailAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Illuminate\Support\Facades\Queue;
use Spatie\QueueableAction\Testing\QueueableActionFake;

beforeEach(function () {
    testInTenantContext();

    Queue::fake();
});

it('can dispatch to customer with payable bills only (subscription based)', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->for(
                    ServiceFactory::new()
                        ->isSubscription()
                        ->withDummyBlueprint()
                )
                ->has(ServiceBillFactory::new()->forPayment())
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    QueueableActionFake::assertPushed(SendToCustomerServiceBillDueDateEmailAction::class);

    QueueableActionFake::assertNotPushed(ExpiredServiceOrderAction::class);
});

it('can dispatch to customer with payable bills only (overdue)', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->for(
                    ServiceFactory::new()
                        ->isSubscription()
                        ->withDummyBlueprint()
                )
                ->has(ServiceBillFactory::new()->dueDate(now())->forPayment())
        )
        ->createOne();

    now()->setTestNow(now()->addDay());

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    QueueableActionFake::assertPushed(ExpiredServiceOrderAction::class);
});

it('cannot dispatch to non-subscription based', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->for(
                    ServiceFactory::new()
                        ->isSubscription(false)
                        ->withDummyBlueprint()
                )
                ->has(ServiceBillFactory::new()->forPayment())
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillDueDateEmailAction::class);
});

it('cannot dispatch non notifiable bill', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->for(
                    ServiceFactory::new()
                        ->isSubscription(false)
                        ->withDummyBlueprint()
                )
                ->has(ServiceBillFactory::new()->forPayment()->billingDate(null))
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillDueDateEmailAction::class);
});

it('cannot dispatch active only customer', function () {
    CustomerFactory::new()
        ->active()
        ->unregistered()
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillDueDateEmailAction::class);
});

it('cannot dispatch registered but inactive customer', function () {
    CustomerFactory::new()
        ->inactive()
        ->registered()
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillDueDateEmailAction::class);
});

it('cannot dispatch inactive service order', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->inactive()
                ->for(
                    ServiceFactory::new()
                        ->isSubscription()
                        ->withDummyBlueprint()
                )
                ->has(ServiceBillFactory::new()->forPayment())
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillDueDateEmailAction::class);
});

it('cannot dispatch closed service order', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->closed()
                ->for(
                    ServiceFactory::new()
                        ->isSubscription()
                        ->withDummyBlueprint()
                )
                ->has(ServiceBillFactory::new()->forPayment())
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillDueDateEmailAction::class);
});

it('cannot dispatch active service order without a bill', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->for(ServiceFactory::new()->isSubscription()->withDummyBlueprint())
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillDueDateEmailAction::class);
});

it('cannot dispatch active service order with bill already paid', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->for(
                    ServiceFactory::new()
                        ->isSubscription()
                        ->withDummyBlueprint()
                )
                ->has(ServiceBillFactory::new()->paid())
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillDueDateEmailAction::class);
});

it('cannot dispatch expired notification to non-automatic termination service', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->continueOnExpiry()
                ->for(
                    ServiceFactory::new()
                        ->isSubscription()
                        ->withDummyBlueprint()
                )
                ->has(ServiceBillFactory::new()->dueDate(now())->forPayment())
        )
        ->createOne();

    now()->setTestNow(now()->addDay());

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    QueueableActionFake::assertNotPushed(ExpiredServiceOrderAction::class);
});
