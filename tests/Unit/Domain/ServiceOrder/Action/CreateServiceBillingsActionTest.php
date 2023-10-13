<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Actions\CreateServiceBillingsAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Jobs\CreateServiceBillJob;
use Illuminate\Support\Facades\Queue;

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
                ->has(ServiceBillFactory::new(['bill_date' => now()])->paid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertPushed(CreateServiceBillJob::class);
});

it('cannot dispatch active only customer', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->unregistered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->has(ServiceBillFactory::new(['bill_date' => now()])->paid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);
});

it('cannot dispatch registered only customer', function () {
    Queue::fake();

    CustomerFactory::new()
        ->inactive()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->has(ServiceBillFactory::new(['bill_date' => now()])->paid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);
});

it('cannot dispatch inactive service order', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->inactive()
                ->has(ServiceBillFactory::new(['bill_date' => now()])->paid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);
});

it('cannot dispatch active service order without bill', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(ServiceOrderFactory::new()->active())
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);
});

it('cannot dispatch active service order with bill but still unpaid', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->has(ServiceBillFactory::new(['bill_date' => now()])->unpaid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);
});

it('cannot dispatch active service order with bill but not past due date yet', function () {
    Queue::fake();

    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->has(ServiceBillFactory::new(['bill_date' => now()->addDay()])->unpaid())
        )
        ->createOne();

    app(CreateServiceBillingsAction::class)->execute();

    Queue::assertNotPushed(CreateServiceBillJob::class);
});
