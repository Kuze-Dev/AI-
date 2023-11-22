<?php

declare(strict_types=1);

use Domain\Address\Database\Factories\AddressFactory;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\ServiceOrder\Actions\CreateServiceOrderAction;
use Domain\ServiceOrder\Actions\CreateServiceOrderAddressAction;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderAddressData;
use Domain\ServiceOrder\DataTransferObjects\ServiceOrderData;
use Domain\ServiceOrder\Models\ServiceOrderAddress;
use Domain\Taxation\Database\Factories\TaxZoneFactory;

it('can create service order addresses', function () {
    testInTenantContext();

    CurrencyFactory::new()->createOne([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'enabled' => true,
    ]);

    $service = ServiceFactory::new()
        ->isActive()
        ->withDummyBlueprint()
        ->createOne();

    $customer = CustomerFactory::new()->createOne();

    $serviceOrder = ServiceOrderFactory::new()->definition();

    $address = AddressFactory::new()->createOne();

    TaxZoneFactory::new(['is_active' => true])
        ->isDefault()
        ->createOne();

    $serviceOrderData = new ServiceOrderData(
        customer_id: $customer->id,
        service_id: $service->id,
        schedule: $serviceOrder['schedule'],
        service_address_id: $address->id,
        billing_address_id: null,
        is_same_as_billing: true,
        additional_charges: $serviceOrder['additional_charges'],
        form: $serviceOrder['customer_form'],
    );

    $serviceOrder = app(CreateServiceOrderAction::class)
        ->execute($serviceOrderData);

    app(CreateServiceOrderAddressAction::class)
        ->execute(new ServiceOrderAddressData(
            serviceOrder: $serviceOrder,
            service_address_id: $serviceOrderData->service_address_id,
            billing_address_id: $serviceOrderData->billing_address_id,
            is_same_as_billing: $serviceOrderData->is_same_as_billing
        ));

    expect(ServiceOrderAddress::get()->count())
        ->toBeGreaterThan(0);
});
