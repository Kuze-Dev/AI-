<?php

declare(strict_types=1);

use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\ServiceOrder\Actions\CreateServiceBillAction;
use Domain\ServiceOrder\Actions\CreateServiceOrderAction;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\DataTransferObjects\ServiceBillData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Models\ServiceBill;
use Filament\Facades\Filament;

use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext();

    Filament::setContext('filament-tenant');

    $this->admin = loginAsSuperAdmin();

    CurrencyFactory::new()->createOne([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'enabled' => true,
    ]);
});

it('can create service bill based on service order', function () {
    $service = ServiceFactory::new()
        ->withDummyBlueprint()
        ->createOne();

    $customer = CustomerFactory::new()->createOne();

    $serviceOrder = ServiceOrderFactory::new()->definition();

    $serviceOrderData = new ServiceOrderData(
        customer_id: $customer->id,
        service_id: $service->id,
        schedule: $serviceOrder['schedule'],
        service_address_id: null,
        billing_address_id: null,
        is_same_as_billing: true,
        additional_charges: $serviceOrder['additional_charges'],
        form: $serviceOrder['customer_form'],
    );

    $serviceOrder = app(CreateServiceOrderAction::class)
        ->execute($serviceOrderData, $this->admin->id);

    $serviceBill = app(CreateServiceBillAction::class)->execute(
        ServiceBillData::fromCreatedServiceOrder($serviceOrder->toArray())
    );

    assertInstanceOf(ServiceBill::class, $serviceBill);
});
