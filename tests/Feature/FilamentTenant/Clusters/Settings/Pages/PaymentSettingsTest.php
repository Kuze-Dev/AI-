<?php

declare(strict_types=1);

use App\Features\Shopconfiguration\PaymentGateway\PaypalGateway;
use App\FilamentTenant\Clusters\Settings\Pages\PaymentSettings;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\Payments\Contracts\PaymentManagerInterface;

use function Pest\Livewire\livewire;

beforeEach(function () {

    testInTenantContext(PaypalGateway::class);

    loginAsSuperAdmin();

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'Paypal']);

    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new PaypalProvider);
});

it('update', function () {

    livewire(PaymentSettings::class)
        ->fillForm([

            'paypal_secret_id' => 'test_paypal_secret_id',
            'paypal_secret_key' => 'test_paypal_secret_d',

        ])
        ->call('save')
        ->assertHasNoFormErrors();
})->todo();
