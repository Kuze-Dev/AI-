<?php

declare(strict_types=1);

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
use Domain\Payments\Events\PaymentProcessEvent;
use Domain\Payments\Models\Payment;
use Domain\Payments\Providers\OfflinePayment;
use Illuminate\Support\Facades\Event;

use function Pest\Laravel\assertDatabaseHas;
use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {

    testInTenantContext();

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'cod', 'gateway' => 'manual']);

    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new OfflinePayment);

});

it('can generate payment authorization dto ', function () {

    $paymentMethod = PaymentMethod::where('slug', 'cod')->first();

    $payment = PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->createOne();

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

    $paymentGateway = app(PaymentManagerInterface::class)->driver($paymentMethod->slug);

    $result = $paymentGateway->withData($providerData)->authorize();

    assertInstanceOf(PaymentAuthorize::class, $result);
});

it('can capture payment ', function () {

    Event::fake([PaymentProcessEvent::class]);

    $paymentMethod = PaymentMethod::where('slug', 'cod')->first();

    $payment = PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->createOne();

    $data = [
        'status' => 'success',
    ];

    $result = app(PaymentManagerInterface::class)->driver($paymentMethod->slug)->capture(
        $payment,
        $data,
    );

    assertInstanceOf(PaymentCapture::class, $result);
    assertDatabaseHas(
        Payment::class,
        [
            'id' => $payment->id,
            'status' => 'paid',
        ]
    );

    Event::assertDispatched(PaymentProcessEvent::class, 1);
});

it('unsupported status must throws InvalidArgumentException class ', function () {

    $paymentMethod = PaymentMethod::where('slug', 'cod')->first();

    $payment = PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->createOne();

    $data = [
        'status' => 'paid',
    ];

    app(PaymentManagerInterface::class)->driver($paymentMethod->slug)->capture(
        $payment,
        $data,
    );

})->throws(InvalidArgumentException::class);
