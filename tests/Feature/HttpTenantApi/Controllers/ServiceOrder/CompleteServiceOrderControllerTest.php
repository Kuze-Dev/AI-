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
    testInTenantContext()->features()->activate(ServiceBase::class);

    $this->customer = CustomerFactory::new()->createOne();

    Sanctum::actingAs($this->customer);

    CurrencyFactory::new()->createOne([
        'code' => 'USD',
        'name' => 'US Dollar',
        'symbol' => '$',
        'enabled' => true,
    ]);
});

it('can store', function () {
    $serviceOrder = ServiceOrderFactory::new()
        ->inProgress()->has(
            ServiceBillFactory::new()
                ->paid()
        )->createOne();

    postJson('api/service-order/complete', [
        'reference_id' => $serviceOrder->reference,
    ])
        ->assertValid()
        ->assertSuccessful();
});
