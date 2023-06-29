<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();
});

it('fetch current customer account', function () {
    $customer = CustomerFactory::new()
        ->createOne();

    Sanctum::actingAs($customer);

    $response = getJson('api/account');

    $response->assertOk()
        ->assertJson(function (AssertableJson $json) use ($customer) {
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
});
