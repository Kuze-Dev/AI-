<?php

declare(strict_types=1);

use App\Features\Shopconfiguration\PaymentGateway\PaypalGateway;
use App\Settings\PaymentSettings;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Database\Factories\PaymentFactory;
use Domain\Payments\DataTransferObjects\AmountData;
use Domain\Payments\DataTransferObjects\PaymentDetailsData;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentAuthorize;
use Domain\Payments\DataTransferObjects\PaymentGateway\PaymentCapture;
use Domain\Payments\DataTransferObjects\ProviderData;
use Domain\Payments\DataTransferObjects\TransactionData;
use Domain\Payments\Providers\PaypalProvider;
use Mockery\MockInterface;

use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {

    testInTenantContext(PaypalGateway::class);

    loginAsSuperAdmin();

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'Paypal', 'slug' => 'paypal']);

    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new PaypalProvider());

    PaymentSettings::fake([
        'paypal_secret_id' => 'test_paypal_secret_id',
        'paypal_secret_key' => 'test_paypal_secret_d',
    ]);
});

it('Paypal payment Gateway must be instance of PaypalProvider  ', function () {

    $paymentGateway = app(PaymentManagerInterface::class)->driver('paypal');

    assertInstanceOf(PaypalProvider::class, $paymentGateway);
});

it('can generate payment authorization dto ', function () {

    $paymentMethod = PaymentMethod::where('slug', 'paypal')->first();

    $payment = PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->create();

    $providerData = new ProviderData(
        transactionData: TransactionData::fromArray([
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

it('can capture payment', function () {

    $paymentMethod = PaymentMethod::where('slug', 'paypal')->first();

    $payment = PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->create();

    $stp = $this->mock(
        PaypalProvider::class,
        function (MockInterface $mock) {

            $mock->expects('capture')->andReturns(new PaymentCapture(true));
        }
    );

    $this->mock(
        PaymentManagerInterface::class,
        fn (MockInterface $mock) => $mock->expects('driver')->andReturns($stp)
    );

    $payment = PaymentFactory::new(['payment_id' => 'ck_test_lkldklsflkdjgkl'])->setPaymentMethod($paymentMethod->id)->create();

    $result = app(PaymentManagerInterface::class)->driver('stripe')->capture($payment, [
        'status' => 'success',
    ]);

    assertInstanceOf(PaymentCapture::class, $result);
});
