<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;

beforeEach(function () {
    testInTenantContext(ServiceBase::class);

    $this->customer = CustomerFactory::new()->createOne();

    Sanctum::actingAs($this->customer);

    CurrencyFactory::new()->createOne([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'enabled' => true,
    ]);
});

it('can show', function () {
    $serviceOrder = ServiceOrderFactory::new()
        ->for($this->customer)
        ->has(ServiceBillFactory::new())
        ->createOne();

    getJson('api/service-order/service-bills/'.$serviceOrder->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) {
            $json
                ->etc();
        });
});
