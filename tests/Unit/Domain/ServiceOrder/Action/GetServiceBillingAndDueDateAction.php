<?php

declare(strict_types=1);

use Carbon\Carbon;
use Domain\Service\Enums\BillingCycle;
use Domain\ServiceOrder\Actions\GetServiceBillingAndDueDateAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;

beforeEach(function () {
    testInTenantContext();
});

$now = now();

$dataSets = [
    /** daily billing cycle */
    [
        BillingCycle::DAILY,        // billing cycle
        5,                          // due date every
        $now->parse('2023-01-01'),  // ordered date
        $now->parse('2023-01-02'),  // bill date
        $now->parse('2023-01-07'),  // due date
    ],
    [
        BillingCycle::DAILY,        // billing cycle
        5,                          // due date every
        $now->parse('2023-01-31'),  // ordered date
        $now->parse('2023-02-01'),  // bill date
        $now->parse('2023-02-06'),  // due date
    ],
    [
        BillingCycle::DAILY,        // billing cycle
        5,                          // due date every
        $now->parse('2023-01-29'),  // ordered date
        $now->parse('2023-01-30'),  // bill date
        $now->parse('2023-02-04'),  // due date
    ],
    /** monthly billing cycle */
    [
        BillingCycle::MONTHLY,      // billing cycle
        13,                         // due date every
        $now->parse('2023-01-31'),  // ordered date
        $now->parse('2023-02-28'),  // bill date
        $now->parse('2023-03-13'),  // due date
    ],
    [
        BillingCycle::MONTHLY,      // billing cycle
        8,                          // due date every
        $now->parse('2023-01-13'),  // ordered date
        $now->parse('2023-02-13'),  // bill date
        $now->parse('2023-02-21'),  // due date
    ],
    [
        BillingCycle::MONTHLY,      // billing cycle
        15,                         // due date every
        $now->parse('2023-03-28'),  // ordered date
        $now->parse('2023-04-28'),  // bill date
        $now->parse('2023-05-13'),  // due date
    ],
    /** yearly billing cycle */
    [
        BillingCycle::YEARLY,       // billing cycle
        15,                         // due date every
        $now->parse('2023-01-01'),  // ordered date
        $now->parse('2024-01-01'),  // bill date
        $now->parse('2024-01-16'),  // due date
    ],
    [
        BillingCycle::YEARLY,       // billing cycle
        16,                         // due date every
        $now->parse('2024-02-29'),  // ordered date
        $now->parse('2025-02-28'),  // bill date
        $now->parse('2025-03-16'),  // due date
    ],
];

it(
    'can get billing dates based on service order',
    function (
        BillingCycle $billingCycle,
        int $dueDateEvery,
        Carbon $createdAt,
        Carbon $billDate,
        Carbon $dueDate
    ) {
        $serviceOrder = ServiceOrderFactory::new()->createOne([
            'billing_cycle' => $billingCycle,
            'due_date_every' => $dueDateEvery,
            'created_at' => $createdAt
        ]);

        $dates = app(GetServiceBillingAndDueDateAction::class)
            ->execute($serviceOrder);

        $dateFormat = 'Y-m-d';

        expect($dates->bill_date->format($dateFormat))
            ->toBe($billDate->format($dateFormat));

        expect($dates->due_date->format($dateFormat))
            ->toBe($dueDate->format($dateFormat));
    }
)
->with($dataSets);

it(
    'can get billing dates based on service bill',
    function (
        BillingCycle $billingCycle,
        int $dueDateEvery,
        Carbon $createdAt,
        Carbon $billDate,
        Carbon $dueDate
    ) {
        $serviceOrder = ServiceOrderFactory::new()->createOne([
            'billing_cycle' => $billingCycle,
            'due_date_every' => $dueDateEvery,
            'created_at' => $createdAt
        ]);

        $serviceBill = ServiceBillFactory::new()->createOne([
            'service_order_id' => $serviceOrder->id,
            'bill_date' => $createdAt,
        ]);

        $dates = app(GetServiceBillingAndDueDateAction::class)
            ->execute($serviceBill);

        $dateFormat = 'Y-m-d';

        expect($dates->bill_date->format($dateFormat))
            ->toBe($billDate->format($dateFormat));

        expect($dates->due_date->format($dateFormat))
            ->toBe($dueDate->format($dateFormat));
    }
)
->with($dataSets);
