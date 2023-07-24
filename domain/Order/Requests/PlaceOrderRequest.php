<?php

declare(strict_types=1);

namespace Domain\Order\Requests;

use Domain\Address\Models\Address;
use Domain\Address\Models\Country;
use Domain\Address\Models\State;
use Domain\Cart\Models\CartLine;
use Domain\Customer\Models\Customer;
use Domain\PaymentMethod\Models\PaymentMethod;
use Domain\ShippingMethod\Models\ShippingMethod;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;

class PlaceOrderRequest extends FormRequest
{
    public function rules()
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

                    $cartLines = CartLine::whereHas('cart', function ($query) {
                        $query->whereBelongsTo(auth()->user());
                    })
                        ->whereCheckoutReference($reference)
                        ->where('checkout_expiration', '>', now())
                        ->whereNull('checked_out_at')
                        ->count();

                    if ( ! $cartLines) {
                        $fail('No cart lines for checkout');

                        return;
                    }
                },
            ],
            'taxations.state_id' => [
                'nullable',
                function ($attribute, $value, $fail) {
                    $customerId = auth()->user()?->id;

                    $customer = Customer::query()
                        ->whereHas('addresses.state', function ($query) use ($value) {
                            $query->where((new State())->getRouteKeyName(), $value);
                        })
                        ->whereId($customerId)
                        ->count();

                    if ($customer == 0) {
                        $fail('Invalid state id');
                    }
                },
            ],
            'taxations.country_id' => [
                'required',
                function ($attribute, $value, $fail) {
                    $customerId = auth()->user()?->id;

                    $customer = Customer::query()
                        ->whereHas('addresses.state.country', function ($query) use ($value) {
                            $query->where((new Country())->getRouteKeyName(), $value);
                        })
                        ->whereId($customerId)
                        ->count();

                    if ($customer == 0) {
                        $fail('Invalid country id');
                    }
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
        ];
    }
}
