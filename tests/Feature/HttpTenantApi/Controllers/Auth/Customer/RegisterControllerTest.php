<?php

declare(strict_types=1);

use Domain\Customer\Models\Customer;
use Domain\Tier\Database\Factories\TierFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\RequestFactories\CustomerRequestFactory;

use function Pest\Laravel\assertDatabaseEmpty;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\postJson;

beforeEach(function () {
    testInTenantContext();
});

it('register', function () {

    TierFactory::createDefault();

    $data = CustomerRequestFactory::new()->create();

    assertDatabaseEmpty(Customer::class);

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

    unset($data['password']);
    $data['birth_date'] .= ' 00:00:00';

    assertDatabaseHas(Customer::class, $data);
});
