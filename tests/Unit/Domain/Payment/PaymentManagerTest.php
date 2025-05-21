<?php

declare(strict_types=1);

use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Providers\OfflinePayment;

use function PHPUnit\Framework\assertInstanceOf;

beforeEach(function () {

    testInTenantContext();

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'Cod']);

    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new OfflinePayment);

});

it('unregister payment method must throw InvalidArgumentException  ', function () {

    app(PaymentManagerInterface::class)->driver('paypal');
})
    ->throws(InvalidArgumentException::class);

it('Manual payment Gateway must be instance of OfflinePayment Provider  ', function () {

    $paymentGateway = app(PaymentManagerInterface::class)->driver('cod');

    assertInstanceOf(OfflinePayment::class, $paymentGateway);
});
