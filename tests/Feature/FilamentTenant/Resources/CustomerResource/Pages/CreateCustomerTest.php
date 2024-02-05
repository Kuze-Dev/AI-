<?php

declare(strict_types=1);

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use App\FilamentTenant\Resources\CustomerResource\Pages\CreateCustomer;
use Domain\Address\Database\Factories\StateFactory;
use Domain\Address\Models\Address;
use Domain\Customer\Models\Customer;
use Domain\Tier\Database\Factories\TierFactory;
use Domain\Tier\Models\Tier;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\RequestFactories\CustomerRequestFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;
use function Pest\Livewire\livewire;

uses()->group('customer');

beforeEach(function () {
    testInTenantContext(
        features: [
            CustomerBase::class,
            AddressBase::class,
            TierBase::class,
        ],
    );
    if (! Tier::whereName(config('domain.tier.default'))->first()) {
        TierFactory::createDefault();
    }
    loginAsSuperAdmin();
});

it('can render page', function () {
    //    livewire(CreateCustomer::class)
    //        ->assertFormExists()
    //        ->assertOk();

    CreateCustomer::getUrl();
})
    ->throws(
        RouteNotFoundException::class,
        'Route [filament.pages.create-customer] not defined.'
    );

//it('can create customer w/ different address', function () {
//
//    $data = CustomerRequestFactory::new()
//        ->withTier(Tier::first())
//        ->withShippingAddress(StateFactory::new()->createOne())
//        ->withBillingAddress(StateFactory::new()->createOne())
//        ->create();
//
//    // to get latest customer
//    travelTo(now()->addSecond());
//
//    livewire(CreateCustomer::class)
//        ->fillForm($data)
//        ->call('create')
//        ->assertHasNoFormErrors()
//        ->assertOk();
//
//    $customer = Customer::latest()->first();
//
//    assertDatabaseHas(Address::class, [
//        'customer_id' => $customer->getKey(),
//        'state_id' => $data['shipping_state_id'],
//        'label_as' => $data['shipping_label_as'],
//        'address_line_1' => $data['shipping_address_line_1'],
//        'zip_code' => $data['shipping_zip_code'],
//        'city' => $data['shipping_city'],
//        'is_default_shipping' => 1,
//        'is_default_billing' => 0,
//    ]);
//
//    assertDatabaseHas(Address::class, [
//        'customer_id' => $customer->getKey(),
//        'state_id' => $data['billing_state_id'],
//        'label_as' => $data['billing_label_as'],
//        'address_line_1' => $data['billing_address_line_1'],
//        'zip_code' => $data['billing_zip_code'],
//        'city' => $data['billing_city'],
//        'is_default_shipping' => 0,
//        'is_default_billing' => 1,
//    ]);
//});
