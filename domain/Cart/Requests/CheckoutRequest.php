<?php

declare(strict_types=1);

namespace Domain\Cart\Requests;

use Domain\Cart\Models\CartLine;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class CheckoutRequest extends FormRequest
{

    public function rules()
    {
        return [
            'cart_line_ids' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {

                    $customerId = auth()->user()?->id;

                    $cartLines = CartLine::with('purchasable')->whereIn('id', $value)
                        ->whereHas('cart', function ($query) use ($customerId) {
                            $query->whereCustomerId($customerId);
                        })
                        ->get();

                    if (count($value) !== $cartLines->count()) {
                        $fail('Invalid cart line IDs.');
                    }
                },
            ],
        ];
    }
}
