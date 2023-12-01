<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\Service\Databases\Factories\ServiceFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderAddressFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Illuminate\Testing\Fluent\AssertableJson;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\getJson;
use function Pest\Laravel\postJson;

beforeEach(function () {
    testInTenantContext()->features()->activate(ServiceBase::class);

    $this->customer = CustomerFactory::new()
        ->hasAddress()
        ->createOne();

    Sanctum::actingAs($this->customer);

    CurrencyFactory::new()->createOne([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'enabled' => true,
    ]);
});

it('can list', function () {
    $serviceOrder = ServiceOrderFactory::new()
        ->for($this->customer)
        ->active()
        ->createOne();

    getJson('api/service-order')
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($serviceOrder) {
            $json
                ->count('data', 1)
                ->where('data.0.id', $serviceOrder->getRouteKey())
                ->etc();
        });
});

it('can show', function () {
    $serviceOrder = ServiceOrderFactory::new()
        ->for($this->customer)
        ->createOne();

    getJson('api/service-order/'.$serviceOrder->getRouteKey())
        ->assertOk()
        ->assertJson(function (AssertableJson $json) use ($serviceOrder) {
            $json
                ->where('data.id', $serviceOrder->getRouteKey())
                ->etc();
        });
});

it('can store', function () {
    $service = ServiceFactory::new()
        ->isActive()
        ->withDummyBlueprint()
        ->createOne();

    $serviceAddress = ServiceOrderAddressFactory::new()
        ->service()
        ->createOne();

    $billingAddress = ServiceOrderAddressFactory::new()
        ->billing()
        ->createOne();

    postJson('api/service-order', [
        'service_id' => $service->id,
        'service_address_id' => $serviceAddress->id,
        'billing_address_id' => $billingAddress->id,
        'is_same_as_billing' => true,
        'schedule' => now()->toString(),
        'form' => [],
        'additional_charges' => [],
    ])
        ->assertValid()
        ->assertSuccessful();
});
