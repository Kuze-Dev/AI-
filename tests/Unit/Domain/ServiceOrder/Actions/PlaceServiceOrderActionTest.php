<?php

declare(strict_types=1);

use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\ServiceOrder\Actions\PlaceServiceOrderAction;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Domain\ServiceOrder\Models\ServiceOrder;

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

    $this->service = ServiceFactory::new()
        ->withDummyBlueprint()
        ->isActive()
        ->createOne();

    $this->customer = CustomerFactory::new()
        ->withAddress()
        ->createOne();

    $address = $this->customer
        ->addresses
        ->first();

    $this->serviceOrder = ServiceOrderFactory::new()->definition();

    $this->data = [
        'customer_id' => $this->customer->id,
        'service_id' => $this->service->id,
        'schedule' => now()
            ->parse($this->serviceOrder['schedule'])
            ->toString(),
        'service_address_id' => $address->id,
        'billing_address_id' => $address->id,
        'is_same_as_billing' => true,
        'additional_charges' => $this->serviceOrder['additional_charges'],
        'form' => $this->serviceOrder['customer_form'],
    ];
});

it('can create using admin', function () {
    $record = app(PlaceServiceOrderAction::class)
        ->execute(
            $this->data,
            $this->customer->id
        );

    assertInstanceOf(ServiceOrder::class, $record);
});

it('can create', function () {
    $record = app(PlaceServiceOrderAction::class)
        ->execute(
            $this->data,
            $this->customer->id
        );

    assertInstanceOf(ServiceOrder::class, $record);
});
