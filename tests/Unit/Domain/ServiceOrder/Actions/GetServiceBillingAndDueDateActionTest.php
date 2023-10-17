<?php

declare(strict_types=1);

use Carbon\Carbon;
use Domain\Service\Enums\BillingCycleEnum;
use Domain\ServiceOrder\Actions\GetServiceBillingAndDueDateAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Database\Factories\ServiceTransactionFactory;
use Domain\ServiceOrder\Exceptions\ServiceBillStatusMusBePaidException;

beforeEach(function () {
    testInTenantContext();

    $this->dateFormat = 'Y-m-d';

    $this->getServiceBillingAndDueDateAction = app(GetServiceBillingAndDueDateAction::class);
});

$now = now();

$dataSets = [
    /** daily billing cycle */
    [
        BillingCycleEnum::DAILY,        // billing cycle
        5,                          // current due date every
        $now->parse('2023-01-01'),  // ordered date
        $now->parse('2023-01-02'),  // current bill date
        $now->parse('2023-01-07'),  // current due date
        $now->parse('2023-01-08'),  // next bill date
        $now->parse('2023-01-13'),  // next due date
    ],
    /** monthly billing cycle */
    [
        BillingCycleEnum::MONTHLY,      // billing cycle
        13,                         // current due date every
        $now->parse('2023-01-31'),  // ordered date
        $now->parse('2023-02-28'),  // current bill date
        $now->parse('2023-03-13'),  // current due date
        $now->parse('2023-04-13'),  // next bill date
        $now->parse('2023-04-26'),  // next due date
    ],
    [
        BillingCycleEnum::MONTHLY,      // billing cycle
        8,                          // current due date every
        $now->parse('2023-01-13'),  // ordered date
        $now->parse('2023-02-13'),  // current bill date
        $now->parse('2023-02-21'),  // current due date
        $now->parse('2023-03-21'),  // next bill date
        $now->parse('2023-03-29'),  // next due date
    ],
    [
        BillingCycleEnum::MONTHLY,      // billing cycle
        15,                         // current due date every
        $now->parse('2023-03-28'),  // ordered date
        $now->parse('2023-04-28'),  // current bill date
        $now->parse('2023-05-13'),  // current due date
        $now->parse('2023-06-13'),  // next bill date
        $now->parse('2023-06-28'),  // next due date
    ],
    // /** yearly billing cycle */
    [
        BillingCycleEnum::YEARLY,       // billing cycle
        15,                         // current due date every
        $now->parse('2023-01-01'),  // ordered date
        $now->parse('2024-01-01'),  // current bill date
        $now->parse('2024-01-16'),  // current due date
        $now->parse('2025-01-16'),  // next bill date
        $now->parse('2025-01-31'),  // next due date
    ],
    [
        BillingCycleEnum::YEARLY,       // billing cycle
        16,                         // current due date every
        $now->parse('2024-02-29'),  // ordered date
        $now->parse('2025-02-28'),  // current bill date
        $now->parse('2025-03-16'),  // current due date
        $now->parse('2026-03-16'),  // next bill date
        $now->parse('2026-04-01'),  // next due date
    ],
];

it('cannot execute', function () {
    $serviceOrder = ServiceOrderFactory::new()
        ->has(
            ServiceBillFactory::new()
                ->forPayment()
                ->has(ServiceTransactionFactory::new())
        )
        ->createOne();

    $serviceBill = $serviceOrder->serviceBills->first();

    $this->getServiceBillingAndDueDateAction->execute(
        $serviceBill,
        $serviceBill->serviceTransaction
    );
})
    ->throws(ServiceBillStatusMusBePaidException::class);

it(
    'can get billing dates based on service bill (on-time)',
    function (
        BillingCycleEnum $billingCycle,
        int $dueDateEvery,
        Carbon $createdAt,
        Carbon $billDate,
        Carbon $dueDate,
        Carbon $nextBillDate,
        Carbon $nextDueDate
    ) {
        //set current date before the bill's due date.
        now()->setTestNow(now()->parse($dueDate)->subDay());

        $serviceOrder = ServiceOrderFactory::new()
            ->has(
                ServiceBillFactory::new([
                    'bill_date' => $billDate,
                    'due_date' => $dueDate,
                ])
                    ->paid()
                    ->has(ServiceTransactionFactory::new())
            )
            ->createOne([
                'billing_cycle' => $billingCycle,
                'due_date_every' => $dueDateEvery,
            ]);

        $serviceBill = $serviceOrder->serviceBills->first();

        $dates = $this->getServiceBillingAndDueDateAction->execute(
            $serviceBill,
            $serviceBill->serviceTransaction
        );

        expect($dates->bill_date->format($this->dateFormat))
            ->toBe($nextBillDate->format($this->dateFormat));

        expect($dates->due_date->format($this->dateFormat))
            ->toBe($nextDueDate->format($this->dateFormat));
    }
)->with($dataSets);

it(
    'can get billing dates based on service bill (late)',
    function (
        BillingCycleEnum $billingCycle,
        int $dueDateEvery,
        Carbon $createdAt,
        Carbon $billDate,
        Carbon $dueDate,
        Carbon $nextBillDate,
        Carbon $nextDueDate
    ) {
        //set current date after the bill's due date.
        now()->setTestNow(now()->parse($dueDate)->addDay());

        $serviceOrder = ServiceOrderFactory::new()
            ->has(
                ServiceBillFactory::new([
                    'bill_date' => $billDate,
                    'due_date' => $dueDate,
                ])
                    ->paid()
                    ->has(ServiceTransactionFactory::new())
            )
            ->createOne([
                'billing_cycle' => $billingCycle,
                'due_date_every' => $dueDateEvery,
            ]);

        $serviceBill = $serviceOrder->serviceBills->first();

        $dates = $this->getServiceBillingAndDueDateAction->execute(
            $serviceBill,
            $serviceBill->serviceTransaction
        );

        expect($dates->bill_date->format($this->dateFormat))
            ->toBe($nextBillDate->addDay()->format($this->dateFormat));

        expect($dates->due_date->format($this->dateFormat))
            ->toBe($nextDueDate->addDay()->format($this->dateFormat));
    }
)->with($dataSets);
