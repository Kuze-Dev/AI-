<?php

declare(strict_types=1);

use App\FilamentTenant\Clusters\Settings\Pages\PaymentSettings;
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
use Domain\Payments\Providers\MayaProvider;
use Mockery\MockInterface;

use function Pest\Livewire\livewire;
use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'Maya']);
    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new MayaProvider);
});

it('Maya payment gateway must be instance of MayaProvider', function () {

    livewire(PaymentSettings::class)
        ->fillForm([

            'maya_publishable_key' => 'pk_test_dllptuueksoicl',
            'maya_secret_key' => 'sk_test_kfjsviefiskuhgkep',
            'maya_production_mode' => false,

        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $paymentGateway = app(PaymentManagerInterface::class)->driver('maya');

    assertInstanceOf(MayaProvider::class, $paymentGateway);
});

it('can generate maya payment authorization DTO', function () {

    livewire(PaymentSettings::class)
        ->fillForm([

            'maya_publishable_key' => 'pk_test_dllptuueksoicl',
            'maya_secret_key' => 'sk_test_kfjsviefiskuhgkep',
            'maya_production_mode' => false,

        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $paymentMethod = PaymentMethod::where('slug', 'maya')->first();

    $payment = PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->create();

    $providerData = new ProviderData(
        transactionData: TransactionData::fromArray([
            'reference_id' => 'ref-123',
            'amount' => AmountData::fromArray([
                'currency' => $payment->currency,
                'total' => $payment->amount,
                'details' => PaymentDetailsData::fromArray($payment->payment_details),
            ]),
        ]),
        paymentModel: $payment,
        payment_method_id: $paymentMethod->id,
    );

    $paymentGateway = app(PaymentManagerInterface::class)->driver('maya');

    $result = $paymentGateway->withData($providerData)->authorize();

    assertInstanceOf(PaymentAuthorize::class, $result);
});

it('can capture maya payment', function () {

    livewire(PaymentSettings::class)
        ->fillForm([

            'maya_publishable_key' => 'pk_test_dllptuueksoicl',
            'maya_secret_key' => 'sk_test_kfjsviefiskuhgkep',
            'maya_production_mode' => false,

        ])
        ->call('save')
        ->assertHasNoFormErrors();

    $paymentMethod = PaymentMethod::where('slug', 'maya')->first();

    $mayaMock = $this->mock(
        MayaProvider::class,
        function (MockInterface $mock) {

            $mock->expects('capture')->andReturns(new PaymentCapture(true));
        }
    );

    $this->mock(
        PaymentManagerInterface::class,
        fn (MockInterface $mock) => $mock->expects('driver')->andReturns($mayaMock)
    );

    $payment = PaymentFactory::new(['payment_id' => 'ck_test_maya'])->setPaymentMethod($paymentMethod->id)->create();

    $result = app(PaymentManagerInterface::class)->driver('maya')->capture($payment, [
        'status' => 'success',
    ]);

    assertInstanceOf(PaymentCapture::class, $result);
});
