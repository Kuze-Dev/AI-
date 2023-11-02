<?php

declare(strict_types=1);

use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\Actions\GetServiceBillingAndDueDateAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Database\Factories\ServiceTransactionFactory;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\Models\ServiceBill;

use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext();

    $this->serviceOrder = ServiceOrderFactory::new()
        ->for(
            ServiceFactory::new()
                ->needsApproval(false)
                ->withDummyBlueprint()
        )
        ->createOne();
});

it('can create', function () {
    $serviceBill = app(CreateServiceBillAction::class)->execute(
        ServiceBillData::initialFromServiceOrder($this->serviceOrder)
    );

    assertInstanceOf(ServiceBill::class, $serviceBill);
});

it('can create with billing and due dates', function () {
    $serviceBill = ServiceBillFactory::new()
        ->paid()
        ->has(ServiceTransactionFactory::new())
        ->createOne();

    $serviceBill = app(CreateServiceBillAction::class)->execute(
        ServiceBillData::subsequentFromServiceOrderWithAssignedDates(
            serviceOrder: $this->serviceOrder,
            serviceOrderBillingAndDueDateData: app(GetServiceBillingAndDueDateAction::class)
                ->execute($serviceBill)
        )
    );

    assertInstanceOf(ServiceBill::class, $serviceBill);
});
