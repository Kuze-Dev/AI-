<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\OrderResource\Pages\ViewOrder;
use Domain\Order\Database\Factories\OrderFactory;
use Domain\PaymentMethod\Database\Factories\PaymentMethodFactory;
use Domain\Payments\Contracts\PaymentManagerInterface;
use Domain\Payments\Database\Factories\PaymentFactory;
use Domain\Payments\Providers\OfflinePayment;
use Domain\ShippingMethod\Database\Factories\ShippingMethodFactory;
use Filament\Facades\Filament;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();

    ShippingMethodFactory::new()->createOne();

    $paymentMethod = PaymentMethodFactory::new()->createOne(['title' => 'cod', 'gateway' => 'manual']);

    app(PaymentManagerInterface::class)->extend($paymentMethod->slug, fn () => new OfflinePayment());

    PaymentFactory::new()->setPaymentMethod($paymentMethod->id)->createOne();
});

it('can render view order details page', function () {
    $order = OrderFactory::new()->createOne();

    $orderDate = Carbon::parse($order->created_at)
        ->setTimezone(Auth::user()?->timezone)
        ->translatedFormat('F d, Y g:i A');

    livewire(ViewOrder::class, ['record' => $order->getRouteKey()])
        ->assertFormExists()
        ->assertSuccessful()
        ->assertFormSet([
            //summary card
            'status' => trans(ucfirst($order->status->value)),
            'created_at' => $orderDate,
            'sub_total' => $order->currency_symbol.' '.number_format($order->sub_total, 2, '.', ','),
            'shipping_total' => $order->currency_symbol.' '.number_format($order->shipping_total, 2, '.', ','),
            'tax_total' => $order->currency_symbol.' '.number_format($order->tax_total, 2, '.', ','),
            'discount_total' => $order->currency_symbol.' '.number_format($order->discount_total, 2, '.', ','),
            'discount_code' => $order->discount_code,
            'total' => $order->currency_symbol.' '.number_format($order->total, 2, '.', ','),
        ])
        ->assertOk()
        ->assertSee([
            //placeholder testing
            $order->orderLines[0]->name,
            $order->orderLines[0]->quantity,
            $order->currency_symbol.' '.number_format($order->sub_total, 2, '.', ','),
        ]);
});
