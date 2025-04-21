<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\OrderResource\Pages\ViewOrder;
use Domain\Order\Database\Factories\OrderFactory;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Database\Factories\PaymentFactory;
use Domain\Payments\Providers\OfflinePayment;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    loginAsSuperAdmin();

    ShippingMethodFactory::new()->createOne();

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'cod', 'gateway' => 'manual']);

    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new OfflinePayment);

    PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->createOne();
});

it('can render view order page', function () {
    $order = OrderFactory::new()->createOne();

    $orderDate = Carbon::parse($order->created_at)
        ->setTimezone(Auth::user()?->timezone)
        ->translatedFormat('F d, Y g:i A');

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertOk()
        ->assertSee([
            // placeholder testing
            trans(ucfirst((string) $order->status->value)),
            $order->customer_first_name,
            $order->customer_last_name,
            $order->customer_email,
            $order->customer_mobile,
            $order->shippingAddress->address_line_1,
            $order->shippingAddress->country,
            $order->shippingAddress->state,
            $order->shippingAddress->city,
            $order->shippingAddress->zip_code,
            $order->billingAddress->address_line_1,
            $order->billingAddress->country,
            $order->billingAddress->state,
            $order->billingAddress->city,
            $order->billingAddress->zip_code,
            $order->payments->first()->paymentMethod?->title,
            $order->shippingMethod->title,
            $orderDate,
        ]);
});
