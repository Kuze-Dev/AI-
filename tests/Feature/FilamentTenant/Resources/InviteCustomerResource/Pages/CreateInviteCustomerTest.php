<?php

declare(strict_types=1);

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use App\FilamentTenant\Resources\InviteCustomerResource\Pages\CreateInviteCustomer;
use Domain\Address\Database\Factories\StateFactory;
use Domain\Customer\Models\Customer;
use Domain\Tier\Database\Factories\TierFactory;
use Domain\Tier\Models\Tier;
use Filament\Facades\Filament;
use Tests\RequestFactories\CustomerRequestFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\travelTo;
use function Pest\Livewire\livewire;

beforeEach(function () {
    $tenant = testInTenantContext();
    $tenant->features()->activate(CustomerBase::class);
    $tenant->features()->activate(AddressBase::class);
    $tenant->features()->activate(TierBase::class);
    Filament::setContext('filament-tenant');
    if (Tier::whereName(config('domain.tier.default'))->doesntExist()) {
        TierFactory::createDefault();
    }
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateInviteCustomer::class)
        ->assertFormExists()
        ->assertOk();
});

it('can create customer'/* w/ same address'*/, function () {

    $data = CustomerRequestFactory::new()
        ->withTier(Tier::first())
        ->withShippingAddress(StateFactory::new()->createOne())
        ->withBillingSameAsShipping()
        ->create();

    // to get latest customer
    travelTo(now()->addSecond());

    livewire(CreateInviteCustomer::class)
        ->fillForm($data)
        ->call('create')
        ->assertHasNoFormErrors()
        ->assertOk();

    $customer = Customer::latest()->first();

    assertDatabaseHas(Customer::class, [
        'id' => $customer->getKey(),
        'email' => $data['email'],
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'mobile' => $data['mobile'],
        'gender' => $data['gender'],
        'status' => $data['status'],
        'birth_date' => $data['birth_date'].' 00:00:00',
    ]);
    //
    //    assertDatabaseHas(Address::class, [
    //        'customer_id' => $customer->getKey(),
    //        'state_id' => $data['shipping_state_id'],
    //        'label_as' => $data['shipping_label_as'],
    //        'address_line_1' => $data['shipping_address_line_1'],
    //        'zip_code' => $data['shipping_zip_code'],
    //        'city' => $data['shipping_city'],
    //        'is_default_shipping' => 1,
    //        'is_default_billing' => 1,
    //    ]);
});
