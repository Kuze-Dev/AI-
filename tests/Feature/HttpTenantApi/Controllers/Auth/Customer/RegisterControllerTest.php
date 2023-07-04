<?php

declare(strict_types=1);

use Domain\Address\Database\Factories\StateFactory;
use Domain\Address\Models\Address;
use Domain\Customer\Enums\Status;
use Domain\Customer\Models\Customer;
use Illuminate\Auth\Events\Registered;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\RequestFactories\CustomerRequestFactory;

use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

beforeEach(function () {
    testInTenantContext();
});

it('register', function () {

    Event::fake(Registered::class);

    $state = StateFactory::new()->createOne();
    $data = CustomerRequestFactory::new()
        ->shippingAddress($state)
        ->billingAddress($state)
        ->create();

    assertDatabaseEmpty(Customer::class);
    assertDatabaseEmpty(Address::class);

    postJson('api/register', $data)
        ->assertValid()
        ->assertCreated()
        ->assertJson(function (AssertableJson $json) {
            $customer = Customer::first();
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
        'status' => Status::ACTIVE->value,
        'birth_date' => $data['birth_date'] . ' 00:00:00',
    ]);

    $customer = Customer::first();

    assertDatabaseCount(Address::class, 2);
    assertDatabaseHas(Address::class, [
        'customer_id' => $customer->getKey(),
        'state_id' => $state->getKey(),
        'label_as' => $data['shipping_label_as'],
        'address_line_1' => $data['shipping_address_line_1'],
        'zip_code' => $data['shipping_zip_code'],
        'city' => $data['shipping_city'],
        'is_default_shipping' => 1,
        'is_default_billing' => 0,
    ]);
    assertDatabaseHas(Address::class, [
        'customer_id' => $customer->getKey(),
        'state_id' => $state->getKey(),
        'label_as' => $data['billing_label_as'],
        'address_line_1' => $data['billing_address_line_1'],
        'zip_code' => $data['billing_zip_code'],
        'city' => $data['billing_city'],
        'is_default_shipping' => 0,
        'is_default_billing' => 1,
    ]);
});

it('register w/ same address', function () {

    $state = StateFactory::new()->createOne();
    $data = CustomerRequestFactory::new()
        ->shippingAddress($state)
        ->billingSameAsShipping()
        ->create();

    assertDatabaseEmpty(Address::class);

    postJson('api/register', $data)
        ->assertValid()
        ->assertCreated();

    $customer = Customer::first();

    assertDatabaseCount(Address::class, 2);
    assertDatabaseHas(Address::class, [
        'customer_id' => $customer->getKey(),
        'state_id' => $state->getKey(),
        'label_as' => $data['shipping_label_as'],
        'address_line_1' => $data['shipping_address_line_1'],
        'zip_code' => $data['shipping_zip_code'],
        'city' => $data['shipping_city'],
        'is_default_shipping' => 1,
        'is_default_billing' => 0,
    ]);
    assertDatabaseHas(Address::class, [
        'customer_id' => $customer->getKey(),
        'state_id' => $state->getKey(),
        'label_as' => $data['shipping_label_as'],
        'address_line_1' => $data['shipping_address_line_1'],
        'zip_code' => $data['shipping_zip_code'],
        'city' => $data['shipping_city'],
        'is_default_shipping' => 0,
        'is_default_billing' => 1,
    ]);
});
