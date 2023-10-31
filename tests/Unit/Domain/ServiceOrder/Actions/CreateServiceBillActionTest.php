<?php

declare(strict_types=1);

use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\Actions\CreateServiceOrderAction;
use Domain\ServiceOrder\Actions\GetServiceBillingAndDueDateAction;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Database\Factories\ServiceTransactionFactory;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Models\ServiceBill;

use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext();

    $this->admin = loginAsSuperAdmin();

    CurrencyFactory::new()->createOne([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'enabled' => true,
    ]);

    $this->serviceOrder = ServiceOrderFactory::new()->createOne();

    $this->customer = CustomerFactory::new()
        ->withAddress()
        ->createOne();

    $this->address = $this->customer
        ->addresses
        ->first();

    $this->service = ServiceFactory::new()
        ->withDummyBlueprint()
        ->isActive()
        ->createOne();
});

it('can create', function () {
    $serviceOrderData = new ServiceOrderData(
        customer_id: $this->customer->id,
        service_id: $this->service->id,
        schedule: $this->serviceOrder->schedule,
        service_address_id: $this->address->id,
        billing_address_id: $this->address->id,
        is_same_as_billing: true,
        additional_charges: $this->serviceOrder->additional_charges,
        form: $this->serviceOrder->customer_form,
    );

    $serviceOrder = app(CreateServiceOrderAction::class)
        ->execute($serviceOrderData);

    $serviceBill = app(CreateServiceBillAction::class)->execute(
        ServiceBillData::fromCreatedServiceOrder($serviceOrder->toArray())
    );

    assertInstanceOf(ServiceBill::class, $serviceBill);
});

it('can create bill billing and due dates', function () {
    $serviceOrderData = new ServiceOrderData(
        customer_id: $this->customer->id,
        service_id: $this->service->id,
        schedule: $this->serviceOrder->schedule,
        service_address_id: $this->address->id,
        billing_address_id: $this->address->id,
        is_same_as_billing: true,
        additional_charges: $this->serviceOrder->additional_charges,
        form: $this->serviceOrder->customer_form,
    );

    $serviceBill = ServiceBillFactory::new()
        ->paid()
        ->has(ServiceTransactionFactory::new())
        ->createOne();

    $serviceOrder = app(CreateServiceOrderAction::class)
        ->execute($serviceOrderData);

    $serviceOrderBillingAndDueDateData = app(GetServiceBillingAndDueDateAction::class)
        ->execute($serviceBill);

    $serviceBill = app(CreateServiceBillAction::class)->execute(
        ServiceBillData::fromCreatedServiceOrder($serviceOrder->toArray()),
        $serviceOrderBillingAndDueDateData
    );

    assertInstanceOf(ServiceBill::class, $serviceBill);
});
