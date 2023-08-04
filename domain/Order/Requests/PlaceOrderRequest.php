<?php

declare(strict_types=1);

namespace Domain\Order\Requests;

use Domain\Address\Models\Address;
use Domain\Cart\Actions\PurchasableCheckerAction;
use Domain\Cart\Models\CartLine;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'addresses.shipping' => [
                'required',
                Rule::exists(Address::class, (new Address())->getRouteKeyName())->where(function ($query) {
                    $customerId = auth()->user()?->id;

                    $query->where('customer_id', $customerId);
                }),
            ],
            'addresses.billing' => [
                'required',
                Rule::exists(Address::class, (new Address())->getRouteKeyName())->where(function ($query) {
                    $customerId = auth()->user()?->id;

                    $query->where('customer_id', $customerId);
                }),
            ],
            'cart_reference' => [
                'required',
                function ($attribute, $value, $fail) {
                    $reference = $value;

                    // $cartLines = CartLine::whereHas('cart', function ($query) {
                    //     $query->whereBelongsTo(auth()->user());
                    // })
                    //     ->whereCheckoutReference($reference)
                    //     ->where('checkout_expiration', '>', now())
                    //     ->whereNull('checked_out_at')
                    //     ->count();

                    // if ( ! $cartLines) {
                    //     $fail('No cart lines for checkout');

                    //     return;
                    // }

                    // $cartLines = CartLine::whereCheckoutReference($reference)->get();

                    // $cartLineIds = array_values($cartLines->pluck('uuid')->toArray());

                    // //auth check
                    // $checkAuth = app(PurchasableCheckerAction::class)->checkAuth($cartLineIds);
                    // if ($checkAuth !== count($cartLineIds)) {
                    //     $fail('Invalid cart line IDs.');
                    // }

                    // //stock check
                    // $checkStocks = app(PurchasableCheckerAction::class)->checkStock($cartLineIds);
                    // if ($checkStocks !== count($cartLineIds)) {
                    //     $fail('Invalid stocks');
                    // }
                },
            ],
            'notes' => [
                'nullable',
                'string',
                'min:1',
                'max:500',
            ],
            'discount_code' => [
                'nullable',
                'string',
                'min:1',
                'max:500',
            ],
            'payment_method' => [
                'required',
                Rule::exists(PaymentMethod::class, (new PaymentMethod())->getRouteKeyName()),
            ],
            'shipping_method' => [
                'required',
                Rule::exists(ShippingMethod::class, (new ShippingMethod())->getRouteKeyName()),
            ],
            'service_id' => [
                'nullable',
                'int',
            ],
        ];
    }
}
