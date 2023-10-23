<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\ServiceOrder\Actions\CreateServiceBillingsAction;
use Domain\ServiceOrder\Actions\SendToCustomerServiceBillEmailAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Jobs\CreateServiceBillJob;
use Illuminate\Support\Facades\Queue;
use Spatie\QueueableAction\Testing\QueueableActionFake;

beforeEach(function () {
    testInTenantContext();
});

it('can dispatch billable customer only', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->for(ServiceFactory::new()->subscriptionBased()->withDummyBlueprint())
                ->has(ServiceBillFactory::new(['bill_date' => now()])->paid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertPushed(CreateServiceBillJob::class);

    QueueableActionFake::assertPushed(SendToCustomerServiceBillEmailAction::class);
});

it('cannot dispatch non subscription service order', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->for(ServiceFactory::new()->nonSubscriptionBased()->withDummyBlueprint())
                ->has(ServiceBillFactory::new(['bill_date' => now()])->paid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillEmailAction::class);
});

it('cannot dispatch active only customer', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->unregistered()
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillEmailAction::class);
});

it('cannot dispatch registered only customer', function () {
    Queue::fake();

    CustomerFactory::new()
        ->inactive()
        ->registered()
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillEmailAction::class);
});

it('cannot dispatch inactive service order', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->inactive()
                ->for(ServiceFactory::new()->subscriptionBased()->withDummyBlueprint())
                ->has(ServiceBillFactory::new(['bill_date' => now()])->paid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillEmailAction::class);
});

it('cannot dispatch closed service order', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->closed()
                ->for(ServiceFactory::new()->subscriptionBased()->withDummyBlueprint())
                ->has(ServiceBillFactory::new(['bill_date' => now()])->paid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillEmailAction::class);
});

it('cannot dispatch active service order without current/latest bill', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->for(ServiceFactory::new()->subscriptionBased()->withDummyBlueprint())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillEmailAction::class);
});

it('cannot dispatch active service order with current/latest bill but still unpaid', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->for(ServiceFactory::new()->subscriptionBased()->withDummyBlueprint())
                ->has(ServiceBillFactory::new(['bill_date' => now()])->unpaid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillEmailAction::class);
});

it('cannot dispatch active service order with current/latest bill but not past due date yet', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->for(ServiceFactory::new()->subscriptionBased()->withDummyBlueprint())
                ->has(ServiceBillFactory::new(['bill_date' => now()->addDay()])->unpaid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);

    QueueableActionFake::assertNotPushed(SendToCustomerServiceBillEmailAction::class);
});
