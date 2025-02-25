<?php

declare(strict_types=1);

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use Domain\Address\Database\Factories\StateFactory;
use Domain\Address\Models\Address;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Domain\Tier\Database\Factories\TierFactory;
use Domain\Tier\Models\Tier;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Event;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\RequestFactories\CustomerRegistrationRequestFactory;

use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;
use function Pest\Laravel\travelTo;

uses()->group('customer');

beforeEach(function () {
    $tenant = testInTenantContext();
    $tenant->features()->activate(CustomerBase::class);
    $tenant->features()->activate(AddressBase::class);
    $tenant->features()->activate(TierBase::class);
    if (! Tier::whereName(config('domain.tier.default'))->first()) {
        TierFactory::createDefault();
    }
});

it('can register with address', function () {

    Event::fake(Registered::class);

    $state = StateFactory::new()->createOne();
    $data = CustomerRegistrationRequestFactory::new()
        ->withShippingAddress($state)
        ->withBillingAddress($state)
        ->create();

    // to get latest customer
    travelTo(now()->addSecond());

    postJson('api/register', $data)
        ->assertValid()
        ->assertCreated()
        ->assertJson(function (AssertableJson $json) {
            $customer = Customer::latest()->first();
            $json
                ->where('data.type', 'customers')
                ->where('data.attributes.first_name', $customer->first_name)
                ->where('data.attributes.last_name', $customer->last_name)
                ->where('data.attributes.email', $customer->email)
                ->where('data.attributes.mobile', $customer->mobile)
                ->where('data.attributes.status', $customer->status->value)
                ->where('data.attributes.birth_date', $customer->birth_date->toDateString())
                ->etc();
        });

    Event::assertDispatched(Registered::class);

    assertDatabaseHas(Customer::class, [
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'email' => $data['email'],
        'mobile' => $data['mobile'],
        'gender' => $data['gender'],
        'status' => Status::ACTIVE->value,
        'birth_date' => $data['birth_date'].' 00:00:00',
        'register_status' => RegisterStatus::REGISTERED,
    ]);

    $customer = Customer::latest()->first();

    assertDatabaseHas(Address::class, [
        'customer_id' => $customer->getKey(),
        'state_id' => $state->getKey(),
        'label_as' => $data['shipping']['label_as'],
        'address_line_1' => $data['shipping']['address_line_1'],
        'zip_code' => $data['shipping']['zip_code'],
        'city' => $data['shipping']['city'],
        'is_default_shipping' => 1,
        'is_default_billing' => 0,
    ]);
    assertDatabaseHas(Address::class, [
        'customer_id' => $customer->getKey(),
        'state_id' => $state->getKey(),
        'label_as' => $data['billing']['label_as'],
        'address_line_1' => $data['billing']['address_line_1'],
        'zip_code' => $data['billing']['zip_code'],
        'city' => $data['billing']['city'],
        'is_default_shipping' => 0,
        'is_default_billing' => 1,
    ]);
});

it('register w/ same address', function () {

    $state = StateFactory::new()->createOne();
    $data = CustomerRegistrationRequestFactory::new()
        ->withShippingAddress($state)
        ->withBillingSameAsShipping()
        ->create();

    // to get latest customer
    travelTo(now()->addSecond());

    postJson('api/register', $data)
        ->assertValid()
        ->assertCreated();

    $customer = Customer::latest()->first();

    assertDatabaseHas(Address::class, [
        'customer_id' => $customer->getKey(),
        'state_id' => $state->getKey(),
        'label_as' => $data['shipping']['label_as'],
        'address_line_1' => $data['shipping']['address_line_1'],
        'zip_code' => $data['shipping']['zip_code'],
        'city' => $data['shipping']['city'],
        'is_default_shipping' => 1,
        'is_default_billing' => 1,
    ]);
});

it('can register with wholesaler tier', function () {

    $tier = app(Tier::class)->firstOrCreate(
        [
            'name' => config('domain.tier.wholesaler-domestic')
        ],[
            'description' => 'wholesaler'
        ]
    );

    $state = StateFactory::new()->createOne();
    $data = CustomerRegistrationRequestFactory::new()
        ->withShippingAddress($state)
        ->withBillingAddress($state)
        ->create([
            'tier_id' => $tier->getKey(),
        ]);

    // to get latest customer
    travelTo(now()->addSecond());

    postJson('api/register', $data)
        ->assertValid()
        ->assertCreated()
        ->assertJson(function (AssertableJson $json) {
            $customer = Customer::latest()->first();
            $json
                ->where('data.type', 'customers')
                ->where('data.attributes.first_name', $customer->first_name)
                ->where('data.attributes.last_name', $customer->last_name)
                ->where('data.attributes.email', $customer->email)
                ->where('data.attributes.mobile', $customer->mobile)
                ->where('data.attributes.status', $customer->status->value)
                ->where('data.attributes.birth_date', $customer->birth_date->toDateString())
                ->etc();
        });

    assertDatabaseHas(Customer::class, [
        'tier_id' => $data['tier_id'],
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'email' => $data['email'],
        'mobile' => $data['mobile'],
        'gender' => $data['gender'],
        'status' => Status::ACTIVE->value,
        'birth_date' => $data['birth_date'].' 00:00:00',
        'register_status' => RegisterStatus::REGISTERED,
    ]);
});

it('can register without address', function () {

    deactivateFeatures(AddressBase::class);

    Event::fake(Registered::class);

    $data = CustomerRegistrationRequestFactory::new()
        ->create();

    // to get latest customer
    travelTo(now()->addSecond());

    postJson('api/register', $data)
        ->assertValid()
        ->assertCreated()
        ->assertJson(function (AssertableJson $json) {
            $customer = Customer::latest()->first();
            $json
                ->where('data.type', 'customers')
                ->where('data.attributes.first_name', $customer->first_name)
                ->where('data.attributes.last_name', $customer->last_name)
                ->where('data.attributes.email', $customer->email)
                ->where('data.attributes.mobile', $customer->mobile)
                ->where('data.attributes.status', $customer->status->value)
                ->where('data.attributes.birth_date', $customer->birth_date->toDateString())
                ->etc();
        });

    Event::assertDispatched(Registered::class);

    assertDatabaseHas(Customer::class, [
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'email' => $data['email'],
        'mobile' => $data['mobile'],
        'gender' => $data['gender'],
        'status' => Status::ACTIVE->value,
        'birth_date' => $data['birth_date'].' 00:00:00',
        'register_status' => RegisterStatus::REGISTERED,
    ]);

});

it('can register with default tier when tier feature is disabled', function () {

    deactivateFeatures(TierBase::class);

    $state = StateFactory::new()->createOne();
    $data = CustomerRegistrationRequestFactory::new()
        ->withShippingAddress($state)
        ->withBillingAddress($state)
        ->create();

    postJson('api/register', $data)
        ->assertValid()
        ->assertCreated();

    assertDatabaseHas(Customer::class, [
        'first_name' => $data['first_name'],
        'last_name' => $data['last_name'],
        'email' => $data['email'],
        'mobile' => $data['mobile'],
        'gender' => $data['gender'],
        'status' => Status::ACTIVE->value,
        'birth_date' => $data['birth_date'].' 00:00:00',
        'register_status' => RegisterStatus::REGISTERED,
        'tier_id' => Tier::whereName(config('domain.tier.default'))->first()->getKey(),
    ]);

});
