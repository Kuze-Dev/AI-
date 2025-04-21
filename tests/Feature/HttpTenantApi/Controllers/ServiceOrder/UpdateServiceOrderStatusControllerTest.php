<?php

declare(strict_types=1);

use App\Features\Service\ServiceBase;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;
use Laravel\Sanctum\Sanctum;

use function Pest\Laravel\postJson;

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

it('can complete', function () {
    $serviceOrder = ServiceOrderFactory::new()
        ->inProgress()
        ->nonSubscriptionBased()
        ->has(
            ServiceBillFactory::new()->paid()
        )->createOne();

    postJson('api/service-order/complete/'.$serviceOrder->reference)
        ->assertValid()
        ->assertSuccessful();
});

it('can close active', function () {
    $serviceOrder = ServiceOrderFactory::new()
        ->active()->subscriptionBased()->has(
            ServiceBillFactory::new()
                ->paid()
        )->createOne();
    // dd($serviceOrder);
    postJson('api/service-order/close/'.$serviceOrder->reference)
        ->assertValid()
        ->assertSuccessful();
});

it('can close inactive', function () {
    $serviceOrder = ServiceOrderFactory::new()
        ->inactive()->subscriptionBased()->has(
            ServiceBillFactory::new()
                ->paid()
        )->createOne();

    postJson('api/service-order/close/'.$serviceOrder->reference)
        ->assertValid()
        ->assertSuccessful();
});
