<?php

declare(strict_types=1);

use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\Payments\Actions\VerifyProofOfPaymentAction;
use Domain\Payments\Database\Factories\PaymentFactory;
use Domain\Payments\DataTransferObjects\VerifyProofOfPaymentData;
use Domain\Payments\Models\Payment;

use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {

    testInTenantContext();

});

it('can check or manual verify proof of payment  ', function () {

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'Cod']);

    $payment = PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->createOne();

    $proofOfPayment = app(VerifyProofOfPaymentAction::class)->execute(
        $payment,
        VerifyProofOfPaymentData::fromArray([
            'remarks' => 'Approved',
            'message' => 'set payment to paid',
        ])
    );

    assertInstanceOf(Payment::class, $proofOfPayment);
});
