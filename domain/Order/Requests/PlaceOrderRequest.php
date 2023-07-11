<?php

declare(strict_types=1);

namespace Domain\Order\Requests;

use Domain\Address\Models\Address;
use Domain\Cart\Models\CartLine;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class PlaceOrderRequest extends FormRequest
{
    public function rules()
    {
        return [
            'addresses.shipping' => [
                'required',
                Rule::exists(Address::class, 'id')->where(function ($query) {
                    $customerId = auth()->user()?->id;

                    $query->where('customer_id', $customerId);
                }),
            ],
            'addresses.billing' => [
                'required',
                Rule::exists(Address::class, 'id')->where(function ($query) {
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

                    if (!$cartLines) {
                        $fail('No cart lines for checkout');
                        return;
                    }
                },
            ],
            'notes' => [
                'nullable',
                'string',
                'min:1',
                'max:500',
            ],
        ];
    }
}
