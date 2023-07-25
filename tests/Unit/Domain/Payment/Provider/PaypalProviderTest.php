<?php

declare(strict_types=1);

use App\FilamentTenant\Pages\Settings\PaymentSettings;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Database\Factories\PaymentFactory;
use Domain\Payments\DataTransferObjects\AmountData;
use Domain\Payments\DataTransferObjects\PaymentDetailsData;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\ProviderData;
use Domain\Payments\DataTransferObjects\TransactionData;
use Domain\Payments\Providers\PaypalProvider;

use function PHPUnit\Framework\assertInstanceOf;
use function Pest\Livewire\livewire;

beforeEach(function () {

    testInTenantContext();

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'Paypal']);

    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new PaypalProvider());

});

it('Paypal payment Gateway must be instance of PaypalProvider  ', function () {

    livewire(PaymentSettings::class)
        ->fillForm([

            'paypal_secret_id' => 'test_paypal_secret_id',
            'paypal_secret_key' => 'test_paypal_secret_d',

        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $paymentGateway = app(PaymentManagerInterface::class)->driver('paypal');

    assertInstanceOf(PaypalProvider::class, $paymentGateway);
});

it('can generate payment authorization dto ', function () {

    livewire(PaymentSettings::class)
        ->fillForm([

            'paypal_secret_id' => 'test_paypal_secret_id',
            'paypal_secret_key' => 'test_paypal_secret_d',

        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $paymentMethod = PaymentMethod::where('slug', 'paypal')->first();

    $payment = PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->create();

    $providerData = new ProviderData(
        transactionData:  TransactionData::fromArray([
            'reference_id' => '123',
            'amount' => AmountData::fromArray([
                'currency' => $payment->currency,
                'total' => $payment->amount,
                'details' => PaymentDetailsData::fromArray($payment->payment_details),
            ]),
        ]),
        paymentModel: $payment,
        payment_method_id: $paymentMethod->id,
    );

    $paymentGateway = app(PaymentManagerInterface::class)->driver('paypal');

    $result = $paymentGateway->withData($providerData)->authorize();

    assertInstanceOf(PaymentAuthorize::class, $result);
});
