<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\OrderResource\Pages\ViewOrder;
use Domain\Order\Database\Factories\OrderFactory;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Database\Factories\PaymentFactory;
use Domain\Payments\Providers\OfflinePayment;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();

    ShippingMethodFactory::new()->createOne();

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'cod', 'gateway' => 'manual']);

    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new OfflinePayment());

    PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->createOne();
});

it('can render view order details page', function () {
    $order = OrderFactory::new()->createOne();

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertSuccessful()
        ->assertOk();
});
