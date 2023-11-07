<?php

declare(strict_types=1);

use App\Settings\ServiceSettings;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\ServiceOrder\Actions\NotifyCustomerServiceBillDueDateAction;
use Domain\ServiceOrder\Actions\SendToCustomerServiceBillDueDateEmailAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Jobs\NotifyCustomerServiceBillDueDateJob;
use Illuminate\Support\Facades\Queue;
use Spatie\QueueableAction\Testing\QueueableActionFake;

beforeEach(function () {
    testInTenantContext();

    Queue::fake();

    ServiceSettings::fake(['days_before_due_date_notification' => 3]);
});

it('can dispatch to customer with payable bills only (subscription based)', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->subscriptionBased()
                ->has(
                    ServiceBillFactory::new()
                        ->pending()
                )
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    Queue::assertPushed(NotifyCustomerServiceBillDueDateJob::class);
});

it('can dispatch on overeached bill date', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->subscriptionBased()
                ->has(
                    ServiceBillFactory::new()
                        ->billingDate(now())
                        ->dueDate(now()->addDay())
                        ->pending()
                )
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    Queue::assertPushed(NotifyCustomerServiceBillDueDateJob::class);
});

it('can dispatch on notification day', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->subscriptionBased()
                ->has(
                    ServiceBillFactory::new()
                        ->billingDate(now()->subDay())
                        ->dueDate(now()->addDays(3))
                        ->pending()
                )
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    Queue::assertPushed(NotifyCustomerServiceBillDueDateJob::class);
});

it('cannot dispatch on neither overeached bill date nor notification day', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->subscriptionBased()
                ->has(
                    ServiceBillFactory::new()
                        ->billingDate(now()->subDay())
                        ->dueDate(now()->addDays(4))
                        ->pending()
                )
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    Queue::assertNotPushed(NotifyCustomerServiceBillDueDateJob::class);
});

it('cannot dispatch to non-subscription based', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->nonSubscriptionBased()
                ->has(
                    ServiceBillFactory::new()
                        ->pending()
                )
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    Queue::assertNotPushed(NotifyCustomerServiceBillDueDateJob::class);
});

it('cannot dispatch non notifiable bill', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->subscriptionBased()
                ->has(
                    ServiceBillFactory::new()
                        ->billingDate(null)
                        ->pending()
                )
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    Queue::assertNotPushed(NotifyCustomerServiceBillDueDateJob::class);
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

    Queue::assertNotPushed(NotifyCustomerServiceBillDueDateJob::class);
});

it('cannot dispatch inactive service order', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->inactive()
                ->subscriptionBased()
                ->has(
                    ServiceBillFactory::new()
                        ->pending()
                )
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    Queue::assertNotPushed(NotifyCustomerServiceBillDueDateJob::class);
});

it('cannot dispatch closed service order', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->closed()
                ->subscriptionBased()
                ->has(
                    ServiceBillFactory::new()
                        ->pending()
                )
        )
        ->createOne();

    app(NotifyCustomerServiceBillDueDateAction::class)->execute();

    Queue::assertNotPushed(NotifyCustomerServiceBillDueDateJob::class);
});

it('cannot dispatch active service order without a bill', function () {
    CustomerFactory::new()
        ->active()
        ->registered()
        ->has(
            ServiceOrderFactory::new()
                ->active()
                ->subscriptionBased()
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
