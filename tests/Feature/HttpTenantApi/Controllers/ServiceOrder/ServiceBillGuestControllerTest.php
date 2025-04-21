<?php

declare(strict_types=1);
use App\Features\Service\ServiceBase;
use Domain\Currency\Database\Factories\CurrencyFactory;
use Domain\Customer\Database\Factories\CustomerFactory;
use Domain\ServiceOrder\Database\Factories\ServiceBillFactory;
use Domain\ServiceOrder\Database\Factories\ServiceOrderFactory;

use function Pest\Laravel\postJson;

beforeEach(function () {
    testInTenantContext(ServiceBase::class);
    $this->customer = CustomerFactory::new()->createOne();
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
        ->createOne();
    $serviceBill = ServiceBillFactory::new([
        'service_order_id' => $serviceOrder->id,
    ])->createOne();
    postJson('api/service-bill-guest', ['reference' => $serviceBill->reference])
        ->assertValid()
        ->assertSuccessful();
});
