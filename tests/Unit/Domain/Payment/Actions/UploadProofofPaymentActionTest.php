<?php

declare(strict_types=1);

use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\Payments\Actions\UploadProofofPaymentAction;
use Domain\Payments\Database\Factories\PaymentFactory;
use Domain\Payments\DataTransferObjects\ProofOfPaymentData;
use Domain\Payments\Models\Payment;
use Illuminate\Http\UploadedFile;

use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {

    testInTenantContext();

});

it('can upload proof of payment  ', function () {

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'Cod']);

    $payment = PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->createOne();

    $proofOfPayment = app(UploadProofofPaymentAction::class)->execute(
        $payment,
        new ProofOfPaymentData(
            UploadedFile::fake()->image('proof_of_payment.jpg')
        )
    );

    assertInstanceOf(Payment::class, $proofOfPayment);
});
