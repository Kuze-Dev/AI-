<?php

declare(strict_types=1);

use App\Features\Shopconfiguration\PaymentGateway\OfflineGateway;
use App\FilamentTenant\Resources\PaymentMethodResource\Pages\EditPaymentMethod;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext(features: OfflineGateway::class);
    loginAsSuperAdmin();
});

it('can render globals', function () {

    $record = PaymentMethodFactory::new()->createOne();

    livewire(EditPaymentMethod::class, ['record' => $record->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful();
});

it('can edit payment method', function () {

    $paymentmethod = PaymentMethodFactory::new()->createOne(['title' => 'Cod']);

    livewire(EditPaymentMethod::class, ['record' => $paymentmethod->getRouteKey()])
        ->fillForm([
            'status' => true,
            'description' => 'Bar',
        ])
        ->call('save')
        ->assertHasNoFormErrors()
        ->assertOk();
});
