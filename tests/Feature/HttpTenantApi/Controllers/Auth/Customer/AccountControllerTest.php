<?php

declare(strict_types=1);

use App\Features\Customer\AddressBase;
use App\Features\Customer\CustomerBase;
use App\Features\Customer\TierBase;
use Domain\Customer\Database\Factories\CustomerFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

uses()->group('customer');

beforeEach(function () {
    $tenant = testInTenantContext();
    $tenant->features()->activate(CustomerBase::class);
    $tenant->features()->activate(AddressBase::class);
    $tenant->features()->activate(TierBase::class);
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
                ->where('data.attributes.cuid', $customer->cuid)
                ->where('data.attributes.first_name', $customer->first_name)
                ->where('data.attributes.last_name', $customer->last_name)
                ->where('data.attributes.email', $customer->email)
                ->where('data.attributes.mobile', $customer->mobile)
                ->where('data.attributes.gender', $customer->gender->value)
                ->where('data.attributes.status', $customer->status->value)
                ->where('data.attributes.birth_date', $customer->birth_date->toDateString())
                ->etc();
        });
});
