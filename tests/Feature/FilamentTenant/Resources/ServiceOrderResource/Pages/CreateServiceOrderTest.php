<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use App\FilamentTenant\Resources\ServiceOrderResource\Pages\CreateServiceOrder;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Service\Databases\Factories\ServiceFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(ServiceBase::class);

    CurrencyFactory::new()->createOne([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'enabled' => true,
    ]);

    loginAsSuperAdmin();
});

it('can render', function () {
    livewire(CreateServiceOrder::class)->assertOk();
});

it('can create product', function () {
    $customer = CustomerFactory::new()
        ->withAddress()
        ->createOne();

    $service = ServiceFactory::new()
        ->isActive()
        ->withDummyBlueprint()
        ->createOne();

    livewire(CreateServiceOrder::class)
        ->fillForm([
            'customer_id' => $customer->id,
            'first_name' => $customer->first_name,
            'last_name' => $customer->last_name,
            'email' => $customer->email,
            'mobile' => $customer->mobile,
            'service_address' => $customer->addresses
                ->first()
                ->id,
            'service_id' => $service->id,
        ])
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk()
        ->instance()
        ->record;
});
