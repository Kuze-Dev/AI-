<?php

declare(strict_types=1);

use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext();

    $this->customer = CustomerFactory::new()->createOne();

    Sanctum::actingAs($this->customer);
});

it('can list service orders', function () {
    $serviceOrder = ServiceOrderFactory::new()->createOne();

    $test = getJson('api/service-order')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($serviceOrder) {
            $json
                ->count('data', 1)
                ->where('data.0.id', $serviceOrder->getRouteKey())
                ->etc();
        });
});
