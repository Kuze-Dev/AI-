<?php

declare(strict_types=1);

use App\Features\Shopconfiguration\PaymentGateway\OfflineGateway;
use App\FilamentTenant\Resources\PaymentMethodResource\Pages\ListPaymentMethods;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(features: OfflineGateway::class);
    loginAsSuperAdmin();
});

it('can render payment methods', function () {
    livewire(ListPaymentMethods::class)
        ->assertSuccessful();
});

it('can list globals', function () {

    $records = PaymentMethodFactory::new()->count(5)->create();

    livewire(ListPaymentMethods::class)
        ->assertCanSeeTableRecords($records);
});
